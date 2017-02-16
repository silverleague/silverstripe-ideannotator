<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

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
