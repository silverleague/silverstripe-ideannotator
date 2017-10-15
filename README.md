# silverstripe-ideannotator

[![Scrutinizer](https://img.shields.io/scrutinizer/g/silverleague/silverstripe-ideannotator.svg)](https://scrutinizer-ci.com/g/silverleague/silverstripe-ideannotator/)
[![Travis](https://img.shields.io/travis/silverleague/silverstripe-ideannotator.svg)](https://travis-ci.org/silverleague/silverstripe-ideannotator)
[![codecov](https://codecov.io/gh/silverleague/silverstripe-ideannotator/branch/master/graph/badge.svg)](https://codecov.io/gh/silverleague/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/dt/silverleague/silverstripe-ideannotator.svg)](https://packagist.org/packages/silverleague/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/v/silverleague/silverstripe-ideannotator.svg)](https://packagist.org/packages/silverleague/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/badge/unstable-dev--master-orange.svg)](https://packagist.org/packages/silverleague/silverstripe-ideannotator)


This module generates @property, @method and @mixin tags for DataObjects, PageControllers and (Data)Extensions, so ide's like PHPStorm recognize the database and relations that are set in the $db, $has_one, $has_many and $many_many arrays.

The docblocks can be generated/updated with each dev/build and with a DataObjectAnnotatorTask per module or classname.

## Installation

Until the repository is fully transferred to SilverLeage, you can install it using the following in your `composer.json`
```json
{
  "require-dev": {
    "silverleague/ideannotator": "3.x-dev"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "git://github.com/silverleague/silverstripe-ideannotator"
    }
  ]
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
