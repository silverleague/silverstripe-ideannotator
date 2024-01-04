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
        $classInfo = new AnnotateClassInfo(TestAnnotatorPage::class);
        $this->assertEquals('silverleague/ideannotator', $classInfo->getModuleName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
