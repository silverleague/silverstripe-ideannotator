<?php

namespace IDEAnnotator\Tests;

use IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;

/**
 * Class DataObjectAnnotatorTest
 *
 * Several tests to make sure the Annotator does it's job correctly
 *
 * @mixin \PHPUnit_Framework_TestCase
 */
class ControllerAnnotatorTest extends \SilverStripe\Dev\SapphireTest
{
    /**
     * @var MockDataObjectAnnotator
     */
    private $annotator;

    /**
     * @var \IDEAnnotator\AnnotatePermissionChecker $permissionChecker
     */
    private $permissionChecker;

    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('IDEAnnotator\Tests\AnnotatorPageTestController', 'extensions',
            array('IDEAnnotator\Tests\AnnotatorPageTest_Extension')
        );

        $this->annotator = Injector::inst()->get('IDEAnnotator\Tests\MockDataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('IDEAnnotator\AnnotatePermissionChecker');
    }

    public function testPageGetsAnnotated()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\AnnotatorPageTest');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\AnnotatorPageTest');

        $this->assertContains(' * Class \IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\AnnotatorPageTestController');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\AnnotatorPageTestController');

        $this->assertContains(' * Class \IDEAnnotator\Tests\AnnotatorPageTestController', $content);
        $this->assertContains('@property \IDEAnnotator\Tests\AnnotatorPageTest dataRecord', $content);
        $this->assertContains('@method \IDEAnnotator\Tests\AnnotatorPageTest data()', $content);
        $this->assertContains('@mixin \IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@mixin \IDEAnnotator\Tests\AnnotatorPageTest_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\AnnotatorPageTest_Extension');
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'IDEAnnotator\Tests\AnnotatorPageTest_Extension');

        $this->assertContains(' * Class \IDEAnnotator\Tests\AnnotatorPageTest_Extension', $annotated);
        $this->assertContains('@property \IDEAnnotator\Tests\AnnotatorPageTestController $owner', $annotated);
    }
}
