// @ts-check

/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  docs: [
    'README',
    {
    // 'README': {
    //   type: 'doc',
    //   id: 'readme',
    // },
    'Getting started': [{
      type: 'autogenerated',
      dirName: 'getting-started',
    }],
    Drupal: [{
      type: 'autogenerated',
      dirName: 'drupal',
    }],
    Workflows: [{
      type: 'autogenerated',
      dirName: 'workflows',
    }],
    Tools: [{
      type: 'autogenerated',
      dirName: 'tools',
    }],
    Integrations: [{
      type: 'autogenerated',
      dirName: 'integrations',
    }],
    Contributing: [{
      type: 'autogenerated',
      dirName: 'contributing',
    }],
}],
};

export default sidebars;