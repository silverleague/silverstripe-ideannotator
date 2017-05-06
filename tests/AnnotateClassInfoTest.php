<?php

use SilverStripe\Dev\SapphireTest;

/**
 * Class DataObjectAnnotatorTest
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class AnnotateClassInfoTest extends SapphireTest
{
    public function testItGetsTheCorrectModuleName()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest');
        $this->assertEquals('ideannotator', $classInfo->getModuleName());
    }

}
