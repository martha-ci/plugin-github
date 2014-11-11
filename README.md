# plugin-github

> GitHub support for Martha Continuous Integration Server

The GitHub plugin provides OAuth authentication for Martha using GitHub,
as well as a project provider that allows you to configure your GitHub
projects to use Martha, and post build plugins to update the commit
status on GitHub.

## Installation

For now, use Composer to install the plugin:

```
composer require martha-ci/plugin-github
```

The plugin must be enabled in your `system.local.php` file:

```
return [
// ...
    'Plugins' => [
        'Martha\Plugin\GitHub' => [

        ]
    ]
// ...
];
```

You'll need to create an application on GitHub for the authentication
piece. See *Settings / Applications / Developer Applications* on GitHub.

Once you have the Client ID and Client Secret, add them to the
`system.local.php` file as configuration options for the plugin:

```
return [
// ...
    'Plugins' => [
        'Martha\Plugin\GitHub' => [

        ]
    ]
// ...
];
```

This installation process will be streamlined in the future.

## Usage

Visiting the `/login` page should now present the user the option to
*Login with GitHub*. When adding a project, *GitHub* should now appear
in the project source drop down.
