<?php

namespace IDEAnnotator\Tests;

use IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * This test should fail, if a DB property is removed from
 * a class, but the property itself still exists after generation
 *
 * @mixin \PHPUnit_Framework_TestCase
 */
class AnnotateChangedDBSpecsTest extends SapphireTest
{
    /**
     * @var MockDataObjectAnnotator
     */
    protected $annotator;

    /**
     * @var \IDEAnnotator\AnnotatePermissionChecker
     */
    protected $permissionChecker;
    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'dev');
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        $this->annotator = Injector::inst()->get('IDEAnnotator\Tests\MockDataObjectAnnotator');
    }

    public function testChangedDBSpecifications()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\TeamChanged');
        $this->assertNotContains('VisitCount', $content);
    }

    public function testNonSupportedTagsWillNotBeTouched()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\TeamChanged');
        $this->assertContains('Simon', $content);
    }

    public function testManuallyCommentedTagsWillNotBeRemoved()
    {

        Config::inst()->update('IDEAnnotator\Tests\TeamChanged', 'extensions', array('IDEAnnotator\Tests\Team_Extension'));

        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\TeamChanged');

        $this->assertContains('The Team Name', $content);
        $this->assertContains('This adds extra methods', $content);
        $this->assertContains('This is the Boss', $content);
    }

}
