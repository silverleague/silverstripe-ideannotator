<?php

namespace SilverLeague\IDEAnnotator\Tests;

use \Page;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class DocBlockMockWithDocBlock
 * Couldn't help it...
 */
class DocBlockMockWithDocBlock extends DataObject implements TestOnly
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)'
    ];
}

/**
 * Class OtherDocBlockMockWithDocBlock
 */
class OtherDocBlockMockWithDocBlock extends DataObject implements TestOnly
{
    /**
     * @var array
     */
    private static $db = [
        'Name' => 'Varchar(255)'
    ];
}

/**
 * StartGeneratedWithDataObjectAnnotator
 *
 * @property string $Street
 * @property int $Nr
 * @property int $PageID
 * @method Page Page()
 *
 * EndGeneratedWithDataObjectAnnotator
 */
class DataObjectWithOldStyleTagMarkers extends DataObject implements TestOnly
{
    /**
     * @var array
     */
    private static $db = [
        'Street' => 'Varchar(255)',
        'Nr'     => 'Int'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Page' => Page::class
    ];
}
