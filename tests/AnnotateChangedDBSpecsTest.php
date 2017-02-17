<?php

namespace Axyr\IDEAnnotator\Tests;

use Axyr\IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;
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
     * @var \Axyr\IDEAnnotator\AnnotatePermissionChecker
     */
    protected $permissionChecker;
    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'dev');
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        $this->annotator = Injector::inst()->get('Axyr\IDEAnnotator\Tests\MockDataObjectAnnotator');
    }

    public function testChangedDBSpecifications()
    {
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'Axyr\IDEAnnotator\Tests\TeamChanged');
        $this->assertNotContains('VisitCount', $content);
    }

    public function testNonSupportedTagsWillNotBeTouched()
    {
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'Axyr\IDEAnnotator\Tests\TeamChanged');
        $this->assertContains('Simon', $content);
    }

    public function testManuallyCommentedTagsWillNotBeRemoved()
    {

        Config::inst()->update('Axyr\IDEAnnotator\Tests\TeamChanged', 'extensions', array('Axyr\IDEAnnotator\Tests\Team_Extension'));

        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'Axyr\IDEAnnotator\Tests\TeamChanged');

        $this->assertContains('The Team Name', $content);
        $this->assertContains('This adds extra methods', $content);
        $this->assertContains('This is the Boss', $content);
    }

}
