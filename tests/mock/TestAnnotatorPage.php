<?php

namespace SilverLeague\IDEAnnotator\Tests;

use Page;
use PageController;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class TestAnnotatorPage extends Page implements TestOnly
{
    private static $db = [
        'SubTitle' => 'Varchar(255)'
    ];
}

class TestAnnotatorPageController extends PageController implements TestOnly
{
    private static $extensions = [
        TestAnnotatorPage_Extension::class
    ];
}

class TestAnnotatorPage_Extension extends Extension implements TestOnly
{
}
