<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class AnnotatorPageTest extends \Page implements TestOnly
{
    private static $db = array(
        'SubTitle'    => 'Varchar(255)'
    );
}

class AnnotatorPageTestController extends \PageController implements TestOnly
{

}

class AnnotatorPageTest_Extension extends Extension implements TestOnly
{

}
