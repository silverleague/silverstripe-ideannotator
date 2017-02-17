<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 * Class DocBlockMockWithDocBlock
 * Couldn't help it...
 */
class DocBlockMockWithDocBlock extends DataObject implements TestOnly
{
    /**
     * @var array
     */
    private static $db = array(
        'Title'    => 'Varchar(255)'
    );
}

/**
 * Class OtherDocBlockMockWithDocBlock
 */
class OtherDocBlockMockWithDocBlock extends DataObject implements TestOnly
{
    /**
     * @var array
     */
    private static $db = array(
        'Name'    => 'Varchar(255)'
    );
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
    private static $db = array(
        'Street' => 'Varchar(255)',
        'Nr'     => 'Int'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Page'    => 'Page'
    );
}
