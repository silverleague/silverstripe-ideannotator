<?php

use SilverStripe\Dev\TestOnly;
use SilverStripe\Core\Extension;


class AnnotatorPageTest extends Page implements TestOnly
{
    private static $db = array(
        'SubTitle'    => 'Varchar(255)'
    );
}

class AnnotatorPageTest_Controller extends Page_Controller implements TestOnly
{

}

class AnnotatorPageTest_Extension extends Extension implements TestOnly
{

}
