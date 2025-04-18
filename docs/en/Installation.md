## Installation
Either run `composer require silverleague/ideannotator --dev`

Or add `silverleague/ideannotator: "*"` to `require-dev` in your composer.json file

Or download and add it to your root directory.


## Config
This module is disabled by default and I recommend to only enable this module in your local development environment, since this module changes the file content of the Dataobject and DataExtension classes.

You can do this, by using something like this in your mysite/_config.php (not recommended!):

```php
if($_SERVER['HTTP_HOST'] == 'mysite.local.dev') {
    Config::modify()->set('SilverLeague\IDEAnnotator\DataObjectAnnotator', 'enabled', true);
}
```

Even when the module is enabled, the generation will only work in a dev environment. Putting a live site into dev with ?isDev will not alter your files.

When enabled IdeAnnotator generates the docblocks on dev/build for mysite only.

You can add extra module folders with the following config setting :

```php

Config::modify()->set('SilverLeague\IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('mysite', 'otherfolderinsiteroot'));
```
or
```yaml

---
Only:
    environment: 'dev'
---
SilverLeague\IDEAnnotator\DataObjectAnnotator:
    enabled_modules:
      - mysite
      - otherfolderinsiteroot
```

If the module you want annotated, has it's own composer.json file, and a name declared, you can enable it like this:

```yaml

---
Only:
    environment: 'dev'
---
SilverLeague\IDEAnnotator\DataObjectAnnotator:
    enabled_modules:
      - mysite
      - SilverLeague/IDEAnnotator
```

If you don't want to use fully qualified classnames, you can configure that like so:

```yaml

---
Only:
    environment: 'dev'
---
SilverLeague\IDEAnnotator\DataObjectAnnotator:
    enabled: true
    use_short_name: true
    enabled_modules:
      - mysite
```

If you want to add extra field types that do not return one of the known values, you can add it as such:

```yaml
SilverLeague\IDEAnnotator\DataObjectAnnotator:
  dbfield_tagnames:
    Symbiote\MultiValueField\ORM\FieldType\MultiValueField: 'MultiValueField|string[]'
```
**NOTE**

- Using short names, will also shorten core names like `ManyManyList`, you'll have to adjust your use statements to work.

- If you change the usage of short names halfway in your project, you may need to clear out all your docblocks before regenerating

### Generics

If you want to enable true generics for DataLists, you can set the `use_generics` parameter to true:

```yaml
SilverLeague\IDEAnnotator\DataObjectAnnotator:
  enabled: true
  use_generics: true
```
