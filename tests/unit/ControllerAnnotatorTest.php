<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PHPUnit_Framework_TestCase;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotateClassInfo;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
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
     * Check if Page is annotated correctly
     */
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

    public function testShortPageGetsAnnotated()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(AnnotatorPageTest::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), AnnotatorPageTest::class);

        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testShortPageControllerGetsAnnotator()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(AnnotatorPageTestController::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(
            file_get_contents($filePath),
            AnnotatorPageTestController::class
        );

        $this->assertContains('@property AnnotatorPageTest dataRecord', $content);
        $this->assertContains('@method AnnotatorPageTest data()', $content);
        $this->assertContains('@mixin AnnotatorPageTest', $content);
        $this->assertContains('@mixin AnnotatorPageTest_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testShortAnnotateControllerExtension()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(AnnotatorPageTest_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, AnnotatorPageTest_Extension::class);

        $this->assertContains(
            '@property AnnotatorPageTestController|AnnotatorPageTest_Extension $owner',
            $annotated
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Setup Defaults
     */
    protected function setUp()
    {
        parent::setUp();
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', false);

        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator']);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }
}
