# silverstripe-ideannotator

[![Build Status](https://secure.travis-ci.org/axyr/silverstripe-ideannotator.png)](https://travis-ci.org/axyr/silverstripe-ideannotator)

This module generates @property, @method and @mixin tags for DataObjects and DataExtensions, so ide's like PHPStorm recognize the database and relations that are set in the $db, $has_one, $has_many and $many_many arrays.

All DataExtensions will be added to the docblock with the @mixin tag.

The docblocks can be generated/updated with each dev/build and with a DataObjectAnnotatorTask per module or classname.

##Example result

```php
<?php

/**
 * StartGeneratedWithDataObjectAnnotator
 * @property string Title
 * @property int Sort
 * @property int AuthorID
 * @method Member Author
 * @method DataList Categories
 * @method ManyManyList Tags
 * @mixin MyDataExtension
 * EndGeneratedWithDataObjectAnnotator
 */
class NewsItem extends DataObject
{
    private static $db = array(
        'Title'	=> 'Varchar(255)',
        'Sort'	=> 'Int'
    );

    private static $has_one = array(
        'Author'    => 'Member'
    );

    private static $has_many = array(
        'Categories' => 'Category'
    );

    private static $many_many = array(
        'Tags'  => 'Tag'
    );
}
```

##Config
This module is disabled by default and I recommend to only enable this module in your local development environment, since this module changes the file content of the Dataobject and DataExtension classes.

You can do this, by using something like this in your mysite/_config.php :

```php
if($_SERVER['HTTP_HOST'] == 'mysite.local.dev') {
    Config::inst()->update('DataObjectAnnotator', 'enabled', true);
}
```
When enabled IdeAnnotator generates the docblocks on dev/build for mysite only.

You can add extra module folders with the following config setting :

```php
Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('mysite', 'otherfolderinsiteroot'));
```
or
```
yml
---
Only:
    environment: 'dev'
---
DataObjectAnnotator:
    enabled_modules:
      - mysite
      - otherfolderinsiteroot
````

##Installation
Either run ```composer require axyr/silverstripe-ideannotator --dev```

Or add ```axyr/silverstripe-ideannotator: "dev-master"``` to `require-dev` in your composer.json file

Or download and add it to your root directory.

##Caution
This module changes the content of your files and currently there is no backup functionality. PHPStorm has a Local history for files and of course you have your code version controlled...
I tried to add complete UnitTests, but I can't garantuee every situation is covered.




