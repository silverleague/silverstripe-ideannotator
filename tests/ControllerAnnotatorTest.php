<?php

namespace Axyr\IDEAnnotator\Tests;

use Axyr\IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;

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
     * @var \Axyr\IDEAnnotator\AnnotatePermissionChecker $permissionChecker
     */
    private $permissionChecker;

    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::modify()->set('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::modify()->set('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::modify()->set('Axyr\IDEAnnotator\Tests\AnnotatorPageTestController', 'extensions',
            array('Axyr\IDEAnnotator\Tests\AnnotatorPageTest_Extension')
        );

        $this->annotator = Injector::inst()->get('Axyr\IDEAnnotator\Tests\MockDataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('Axyr\IDEAnnotator\AnnotatePermissionChecker');
    }

    public function testPageGetsAnnotated()
    {
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\AnnotatorPageTest');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'Axyr\IDEAnnotator\Tests\AnnotatorPageTest');

        $this->assertContains(' * Class \Axyr\IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\AnnotatorPageTestController');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'Axyr\IDEAnnotator\Tests\AnnotatorPageTestController');

        $this->assertContains(' * Class \Axyr\IDEAnnotator\Tests\AnnotatorPageTestController', $content);
        $this->assertContains('@property \Axyr\IDEAnnotator\Tests\AnnotatorPageTest dataRecord', $content);
        $this->assertContains('@method \Axyr\IDEAnnotator\Tests\AnnotatorPageTest data()', $content);
        $this->assertContains('@mixin \Axyr\IDEAnnotator\Tests\AnnotatorPageTest', $content);
        $this->assertContains('@mixin \Axyr\IDEAnnotator\Tests\AnnotatorPageTest_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo('Axyr\IDEAnnotator\Tests\AnnotatorPageTest_Extension');
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'Axyr\IDEAnnotator\Tests\AnnotatorPageTest_Extension');

        $this->assertContains(' * Class \Axyr\IDEAnnotator\Tests\AnnotatorPageTest_Extension', $annotated);
        $this->assertContains('@property \Axyr\IDEAnnotator\Tests\AnnotatorPageTestController $owner', $annotated);
    }
}
