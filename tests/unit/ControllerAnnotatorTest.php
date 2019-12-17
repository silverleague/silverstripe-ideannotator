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
        $classInfo = new AnnotateClassInfo(TestAnnotatorPage::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), TestAnnotatorPage::class);

        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo(TestAnnotatorPageController::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(
            file_get_contents($filePath),
            TestAnnotatorPageController::class
        );

        $this->assertContains('@property \SilverLeague\IDEAnnotator\Tests\TestAnnotatorPage dataRecord', $content);
        $this->assertContains('@method \SilverLeague\IDEAnnotator\Tests\TestAnnotatorPage data()', $content);
        $this->assertContains('@mixin \SilverLeague\IDEAnnotator\Tests\TestAnnotatorPage', $content);
        $this->assertContains('@mixin \SilverLeague\IDEAnnotator\Tests\TestAnnotatorPage_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo(TestAnnotatorPage_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, TestAnnotatorPage_Extension::class);

        $this->assertContains(
            '@property \SilverLeague\IDEAnnotator\Tests\TestAnnotatorPageController|\SilverLeague\IDEAnnotator\Tests\TestAnnotatorPage_Extension $owner',
            $annotated
        );
    }

    public function testShortPageGetsAnnotated()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(TestAnnotatorPage::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), TestAnnotatorPage::class);

        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testShortPageControllerGetsAnnotator()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(TestAnnotatorPageController::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(
            file_get_contents($filePath),
            TestAnnotatorPageController::class
        );

        $this->assertContains('@property TestAnnotatorPage dataRecord', $content);
        $this->assertContains('@method TestAnnotatorPage data()', $content);
        $this->assertContains('@mixin TestAnnotatorPage', $content);
        $this->assertContains('@mixin TestAnnotatorPage_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testShortAnnotateControllerExtension()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);
        $classInfo = new AnnotateClassInfo(TestAnnotatorPage_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, TestAnnotatorPage_Extension::class);

        $this->assertContains(
            '@property TestAnnotatorPageController|TestAnnotatorPage_Extension $owner',
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
