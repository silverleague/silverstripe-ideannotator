# silverstripe-ideannotator

[![Travis](https://img.shields.io/travis/axyr/silverstripe-ideannotator.svg)](https://travis-ci.org/axyr/silverstripe-ideannotator)
[![Code Climate](https://img.shields.io/codeclimate/github/axyr/silverstripe-ideannotator.svg)](https://codeclimate.com/github/axyr/silverstripe-ideannotator/)
[![Packagist](https://img.shields.io/packagist/v/axyr/silverstripe-ideannotator.svg)](https://packagist.org/packages/axyr/silverstripe-ideannotator)
[![Packagist](https://img.shields.io/packagist/dt/axyr/silverstripe-ideannotator.svg)](https://packagist.org/packages/axyr/silverstripe-ideannotator)

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

## Further information
For installation, see [installation](docs/en/Installation.md)

For the Code of Conduct, see [CodeOfConduct](docs/en/CodeOfConduct.md)

For contributing, see [Contributing](CONTRIBUTING.md)

For further documentation information, see the [docs](docs/en/Index.md)

##Caution
This module changes the content of your files and currently there is no backup functionality. PHPStorm has a Local history for files and of course you have your code version controlled...
I tried to add complete UnitTests, but I can't garantuee every situation is covered.




