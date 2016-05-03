<?php


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
        Config::inst()->update('DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('AnnotatorPageTest_Controller', 'extensions',
            array('AnnotatorPageTest_Extension')
        );

        $this->annotator = Injector::inst()->get('MockDataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
    }

    public function testPageGetsAnnotated()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'AnnotatorPageTest');

        $this->assertContains(' * Class AnnotatorPageTest', $content);
        $this->assertContains('@property string $SubTitle', $content);
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest_Controller');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'AnnotatorPageTest_Controller');

        $this->assertContains(' * Class AnnotatorPageTest_Controller', $content);
        $this->assertContains('@property AnnotatorPageTest dataRecord', $content);
        $this->assertContains('@method AnnotatorPageTest data()', $content);
        $this->assertContains('@mixin AnnotatorPageTest', $content);
        $this->assertContains('@mixin AnnotatorPageTest_Extension', $content);
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest_Extension');
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'AnnotatorPageTest_Extension');

        $this->assertContains(' * Class AnnotatorPageTest_Extension', $annotated);
        $this->assertContains('@property AnnotatorPageTest_Controller $owner', $annotated);
    }
}
