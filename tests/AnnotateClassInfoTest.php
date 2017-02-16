<?php

namespace Axyr\IDEAnnotator\Tests;

use Axyr\IDEAnnotator\AnnotateClassInfo;
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
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\AnnotatorPageTest');
        $this->assertEquals('ideannotator', $classInfo->getModuleName());
    }

}
