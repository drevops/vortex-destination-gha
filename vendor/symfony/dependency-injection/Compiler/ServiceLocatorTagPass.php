<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Applies the "container.service_locator" tag by wrapping references into ServiceClosureArgument instances.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class ServiceLocatorTagPass extends AbstractRecursivePass
{
    use PriorityTaggedServiceTrait;

    protected bool $skipScalars = true;

    protected function processValue(mixed $value, bool $isRoot = false): mixed
    {
        if ($value instanceof ServiceLocatorArgument) {
            if ($value->getTaggedIteratorArgument()) {
                $value->setValues($this->findAndSortTaggedServices($value->getTaggedIteratorArgument(), $this->container));
            }

            return self::register($this->container, $value->getValues());
        }

        if ($value instanceof Definition) {
            $value->setBindings(parent::processValue($value->getBindings()));
        }

        if (!$value instanceof Definition || !$value->hasTag('container.service_locator')) {
            return parent::processValue($value, $isRoot);
        }

        if (!$value->getClass()) {
            $value->setClass(ServiceLocator::class);
        }

        $values = $value->getArguments()[0] ?? null;
        $services = [];

        if ($values instanceof TaggedIteratorArgument) {
            foreach ($this->findAndSortTaggedServices($values, $this->container) as $k => $v) {
                $services[$k] = new ServiceClosureArgument($v);
            }
        } elseif (!\is_array($values)) {
            throw new InvalidArgumentException(\sprintf('Invalid definition for service "%s": an array of references is expected as first argument when the "container.service_locator" tag is set.', $this->currentId));
        } else {
            $i = 0;

            foreach ($values as $k => $v) {
                if ($v instanceof ServiceClosureArgument) {
                    $services[$k] = $v;
                    continue;
                }

                if ($i === $k) {
                    if ($v instanceof Reference) {
                        $k = (string) $v;
                    }
                    ++$i;
                } elseif (\is_int($k)) {
                    $i = null;
                }

                $services[$k] = new ServiceClosureArgument($v);
            }
            if (\count($services) === $i) {
                ksort($services);
            }
        }

        $value->setArgument(0, $services);

        $id = '.service_locator.'.ContainerBuilder::hash($value);

        if ($isRoot) {
            if ($id !== $this->currentId) {
                $this->container->setAlias($id, new Alias($this->currentId, false));
            }

            return $value;
        }

        $this->container->setDefinition($id, $value->setPublic(false));

        return new Reference($id);
    }

    public static function register(ContainerBuilder $container, array $map, ?string $callerId = null): Reference
    {
        foreach ($map as $k => $v) {
            $map[$k] = new ServiceClosureArgument($v);
        }

        $locator = (new Definition(ServiceLocator::class))
            ->addArgument($map)
            ->addTag('container.service_locator');

        if (null !== $callerId && $container->hasDefinition($callerId)) {
            $locator->setBindings($container->getDefinition($callerId)->getBindings());
        }

        if (!$container->hasDefinition($id = '.service_locator.'.ContainerBuilder::hash($locator))) {
            $container->setDefinition($id, $locator);
        }

        if (null !== $callerId) {
            $locatorId = $id;
            // Locators are shared when they hold the exact same list of factories;
            // to have them specialized per consumer service, we use a cloning factory
            // to derivate customized instances from the prototype one.
            $container->register($id .= '.'.$callerId, ServiceLocator::class)
                ->setFactory([new Reference($locatorId), 'withContext'])
                ->addTag('container.service_locator_context', ['id' => $callerId])
                ->addArgument($callerId)
                ->addArgument(new Reference('service_container'));
        }

        return new Reference($id);
    }
}
