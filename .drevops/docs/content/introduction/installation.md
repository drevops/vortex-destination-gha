# Installation

## Installation

```shell title="Install using interactive prompt"
curl -SsL https://install.drevops.com | php
```

[//]: # (@TODO Add recording of the installation process)

```shell title="Quiet installation"
curl -SsL https://install.drevops.com | php -- --quiet
```

```shell title="Installation into a specific directory"
curl -SsL https://install.drevops.com | php -- /destination/directory
```

!!! note "Work in progress"

    We are looking for a way to make the installation process more user-friendly
    and support `composer create-project` command.

## Updating

```shell title="Update to the latest version"
ahoy update-drevops
```

```shell title="Update to the specific commit"
ahoy update-drevops cb9979b2c10c59d52874be4661e9331b01d9b7c5
```

If you have modified any of the files that are managed by DrevOps, the update
will override them. You would need to re-apply your changes. This is because
DrevOps is not aware of the changes you have made (and this is a good thing
because you don't want to have your changes overwritten by mistake without a
proper review).