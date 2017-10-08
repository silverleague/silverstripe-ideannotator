<?php

namespace SilverLeague\IDEAnnotator\Tests;

// Why is this required?
// @todo get it to work properly
require_once(BASE_PATH . '/mysite/code/Page.php');
require_once(BASE_PATH . '/mysite/code/PageController.php');

use Page;
use PageController;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class AnnotatorPageTest extends Page implements TestOnly
{
    private static $db = [
        'SubTitle' => 'Varchar(255)'
    ];
}

class AnnotatorPageTestController extends PageController implements TestOnly
{
    private static $extensions = [
        AnnotatorPageTest_Extension::class
    ];
}

class AnnotatorPageTest_Extension extends Extension implements TestOnly
{
}
