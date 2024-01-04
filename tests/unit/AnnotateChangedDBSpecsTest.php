<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PHPUnit_Framework_TestCase;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotateClassInfo;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * This test should fail, if a DB property is removed from
 * a class, but the property itself still exists after generation
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class AnnotateChangedDBSpecsTest extends SapphireTest
{
    /**
     * @var MockDataObjectAnnotator
     */
    protected $annotator;

    /**
     * @var AnnotatePermissionChecker
     */
    protected $permissionChecker;

    public function testChangedDBSpecifications()
    {
        $classInfo = new AnnotateClassInfo(TeamChanged::class);
        $filePath = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), TeamChanged::class);
        $this->assertNotContains('VisitCount', $content);
    }

    public function testNonSupportedTagsWillNotBeTouched()
    {
        $classInfo = new AnnotateClassInfo(TeamChanged::class);
        $filePath = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), TeamChanged::class);
        $this->assertContains('Simon', $content);
    }

    public function testManuallyCommentedTagsWillNotBeRemoved()
    {
        Config::modify()->set(TeamChanged::class, 'extensions', [Team_Extension::class]);

        $classInfo = new AnnotateClassInfo(TeamChanged::class);
        $filePath = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), TeamChanged::class);

        $this->assertContains('The Team Name', $content);
        $this->assertContains('This adds extra methods', $content);
        $this->assertContains('This is the Boss', $content);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Setup Defaults
     */
    protected function setUp(): void
    {
        parent::setUp();
        Config::modify()->set(Director::class, 'environment_type', 'dev');
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator']);
        Config::modify()->merge(TeamChanged::class, 'extensions', [Team_Extension::class]);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
    }
}
