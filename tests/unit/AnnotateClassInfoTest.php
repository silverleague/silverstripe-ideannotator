<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PHPUnit_Framework_TestCase;
use SilverLeague\IDEAnnotator\Helpers\AnnotateClassInfo;
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
        $classInfo = new AnnotateClassInfo(AnnotatorPageTest::class);
        $this->assertEquals('silverleague/ideannotator', $classInfo->getModuleName());
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
