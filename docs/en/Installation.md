# Installation

Install with composer:

```
composer require --dev silverleague/ideannotator
```

If you know the version you want to use already, you can also add this to `composer.json`:

```
"require-dev": {
    "silverleague/ideannotator": "^2.0"
}
```

## Configuration

This module is disabled by default and it is recommended to only enable this module in your local development environment, since this module changes the file content of the `DataObject` and `DataExtension` classes.

You can do this by using something like this in your `mysite/_config.php`:

```php
if ($_SERVER['HTTP_HOST'] == 'mysite.local.dev') {
    Config::inst()->update('DataObjectAnnotator', 'enabled', true);
}
```

Even when the module is enabled, the generation will only work in a "dev" environment. Putting a live site into dev with `?isDev` will not alter your files.

When enabled, IdeAnnotator generates the docblocks on `dev/build` for the `/mysite` folder only.

You can add extra module folders with the following config setting:

```php
Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('mysite', 'otherfolderinsiteroot'));
```
or via YAML:

```yml
---
Only:
    environment: 'dev'
---
DataObjectAnnotator:
  enabled_modules:
    - mysite
    - otherfolderinsiteroot
```
