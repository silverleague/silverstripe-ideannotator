<?php

/**
 * Class AnnotatorDocBlockTest
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class AnnotatorDocBlockTest extends SapphireTest
{

    public function testClassHasAnExistingDocBlock()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Player');
        $this->assertTrue((bool)strpos($classInfo->getDocComment(), 'DataObjectAnnotatorTest_Player'));

        $classInfo = new AnnotateClassInfo('DocBlockMockWithoutDocBlock');
        $this->assertFalse((bool)strpos($classInfo->getDocComment(), 'DocBlockMockWithoutDocBlock'));
    }

}
