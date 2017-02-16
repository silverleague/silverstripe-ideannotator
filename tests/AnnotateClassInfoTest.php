<?php

namespace IDEAnnotator\Tests;

use IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Dev\SapphireTest;

/**
 * Class DataObjectAnnotatorTest
 *
 * @mixin \PHPUnit_Framework_TestCase
 */
class AnnotateClassInfoTest extends SapphireTest
{
    public function testItGetsTheCorrectModuleName()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\AnnotatorPageTest');
        $this->assertEquals('ideannotator', $classInfo->getModuleName());
    }

}
