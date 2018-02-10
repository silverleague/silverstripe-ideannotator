<?php

namespace SilverLeague\IDEAnnotator\Tests;

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
