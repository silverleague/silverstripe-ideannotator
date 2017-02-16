<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class DoubleDataObjectInOneFile1 extends DataObject implements TestOnly
{
    private static $db = array(
        'Title'    => 'Varchar(255)'
    );
}

class DoubleDataObjectInOneFile2 extends DataObject implements TestOnly
{
    private static $db = array(
        'Name'    => 'Varchar(255)'
    );
}
