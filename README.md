# silverstripe-ideannotator

![Build Status](https://github.com/silverleague/silverstripe-ideannotator/actions/workflows/ci.yml/badge.svg)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/silverleague/silverstripe-ideannotator.svg)](https://scrutinizer-ci.com/g/silverleague/silverstripe-ideannotator/)
[![codecov](https://codecov.io/gh/silverleague/silverstripe-ideannotator/branch/master/graph/badge.svg)](https://codecov.io/gh/silverleague/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/dt/silverleague/ideannotator.svg)](https://packagist.org/packages/silverleague/ideannotator)
[![Packagist](https://img.shields.io/packagist/v/silverleague/ideannotator.svg)](https://packagist.org/packages/silverleague/ideannotator)
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/silverleague/ideannotator.svg)](https://packagist.org/packages/silverleague/ideannotator)


This module generates @property, @method and @mixin tags for DataObjects, PageControllers and (Data)Extensions, so ide's like PHPStorm recognize the database and relations that are set in the $db, $has_one, $has_many and $many_many arrays.

The docblocks can be generated/updated with each dev/build and with a DataObjectAnnotatorTask per module or classname.

## Requirements

SilverStripe Framework and possible custom code.

By default, `mysite` and `app` are enabled "modules".

### Version ^2:
SilverStripe 3.x framework

### Version ^3:
Silverstripe 4.x, 5,x

### Version ^4:
Silverstripe 6.x+

## Installation

```json
{
  "require-dev": {
    "silverleague/ideannotator": "^4"
  }
}
```
Please note, this example omitted any possible modules you require yourself!

## Example result

```php
<?php

/**
 * Class NewsItem
 *
 * @property string $Title
 * @property int $Sort
 * @property int $Version
 * @property int $AuthorID
 * @method \SilverStripe\Security\Member Author()
 * @method \SilverStripe\ORM\DataList|Category[] Categories()
 * @method \SilverStripe\ORM\ManyManyList|Tag[] Tags()
 * @mixin Versioned
 */
class NewsItem extends \SilverStripe\ORM\DataObject
{
    private static $db = array(
        'Title'	=> 'Varchar(255)',
        'Sort'	=> 'Int'
    );

    private static $has_one = array(
        'Author' => Member::class
    );

    private static $has_many = array(
        'Categories' => Category::class
    );

    private static $many_many = array(
        'Tags' => Tag::class
    );
}
```

## Further information
For installation, see [installation](docs/en/Installation.md)

For the Code of Conduct, see [CodeOfConduct](docs/en/CodeOfConduct.md)

For contributing, see [Contributing](CONTRIBUTING.md)

For further documentation information, see the [docs](docs/en/Index.md)

## A word of caution
This module changes the content of your files and currently there is no backup functionality. PHPStorm has a Local history for files and of course you have your code version controlled...
I tried to add complete UnitTests, but I can't garantuee every situation is covered.

Windows users should be aware that the PHP Docs are generated with PSR in mind and use \n for line endings rather than Window's \r\n, some editors may have a hard time with these line endings.

This module should **never** be installed on a production environment.
