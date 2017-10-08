<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PHPUnit_Framework_TestCase;
use SilverLeague\IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;
use SilverLeague\IDEAnnotator\AnnotatePermissionChecker;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Dev\SapphireTest;

/**
 * Class DataObjectAnnotatorTest
 *
 * Several tests to make sure the Annotator does it's job correctly
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class ControllerAnnotatorTest extends SapphireTest
{
    /**
     * @var MockDataObjectAnnotator
     */
    private $annotator;

    /**
     * @var AnnotatePermissionChecker $permissionChecker
     */
    private $permissionChecker;

    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator']);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }

    public function testPageGetsAnnotated()
    {
        $classInfo = new AnnotateClassInfo(AnnotatorPageTest::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), AnnotatorPageTest::class);

        $this->assertContains(' * Class \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo(AnnotatorPageTestController::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(
            file_get_contents($filePath),
            AnnotatorPageTestController::class
        );

        $this->assertContains(' * Class \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTestController', $content);
        $this->assertContains('@property \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest dataRecord', $content);
        $this->assertContains('@method \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest data()', $content);
        $this->assertContains('@mixin \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@mixin \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo(AnnotatorPageTest_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, AnnotatorPageTest_Extension::class);

        $this->assertContains(' * Class \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest_Extension', $annotated);
        $this->assertContains(
            '@property \SilverLeague\IDEAnnotator\Tests\AnnotatorPageTestController|\SilverLeague\IDEAnnotator\Tests\AnnotatorPageTest_Extension $owner',
            $annotated
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
