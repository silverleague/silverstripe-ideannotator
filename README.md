# silverstripe-ideannotator

[![Travis branch](https://img.shields.io/travis/silverleague/silverstripe-ideannotator/master.svg)](https://github.com/silverleague/silverstripe-ideannotator)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/silverleague/silverstripe-ideannotator.svg)](https://scrutinizer-ci.com/g/silverleague/silverstripe-ideannotator/)
[![Codecov branch](https://img.shields.io/codecov/c/github/silverleague/silverstripe-ideannotator/master.svg)](https://github.com/silverleague/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/dt/axyr/silverstripe-ideannotator.svg)](https://packagist.org/packages/axyr/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/v/axyr/silverstripe-ideannotator.svg)](https://packagist.org/packages/axyr/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/badge/unstable-dev--master-orange.svg)](https://packagist.org/packages/axyr/silverstripe-ideannotator)


This module generates `@property`, `@method` and `@mixin` tags for sub classes of `DataObject`, `Page_Controller` and `Extension`/`DataExtension` so that IDEs like PHPStorm can recognize the database fields and relations that are set in `$db`, `$has_one`, `$has_many` and `$many_many` arrays.

The docblocks can be generated/updated with each `dev/build` and with a `DataObjectAnnotatorTask` per module or classname.

## Requirements

* PHP 5.3.3+
* SilverStripe ^3.1
* Composer

## Installation

Install with composer:

```
composer require silverleague/ideannotator
```

## Maintainers

- Martijn @axyr van Nieuwenhoven - Originator and original maintainer
- Simon @Firesphere Erkelens - SilverLeague maintainer

No changes with serious impact will be accepted without either Martijn or Simon's approval.

Big thanks to @axyr for the initial work to create and maintain this module. It is very much appreciated by the community.


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
 * @method Member Author()
 * @method DataList|Category[] Categories()
 * @method ManyManyList|Tag[] Tags()
 * @mixin Versioned
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

## Further information

For further information please see the [user guide documentation](docs/en/Index.md).

Please see [the contributing guide](CONTRIBUTING.md) for more information on how to contribute to this project.

## Caution

This module changes the content of your files and currently there is no backup functionality. PHPStorm has a Local history for files and of course you have your code version controlled... Unit tests are in place, but it can't be guaranteed that every situation is covered.

Please use carefully, and only install in development environments.
