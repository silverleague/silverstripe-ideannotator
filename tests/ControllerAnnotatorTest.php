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
        $filePath  = $classInfo->getWritableClassFilePath();

        $content = $this->annotator->writeDocBlock(file_get_contents($filePath), 'AnnotatorPageTest');

        $this->assertTrue((bool)strpos($content, ' * Class AnnotatorPageTest'));
        $this->assertTrue((bool)strpos($content, '@property string $SubTitle'));
    }

    public function testPageControllerGetsAnnotator()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest_Controller');
        $filePath  = $classInfo->getWritableClassFilePath();

        $content = $this->annotator->writeDocBlock(file_get_contents($filePath), 'AnnotatorPageTest_Controller');
        $this->assertTrue((bool)strpos($content, '@property AnnotatorPageTest dataRecord'));
        $this->assertTrue((bool)strpos($content, '@method AnnotatorPageTest data()'));
        $this->assertTrue((bool)strpos($content, '@mixin AnnotatorPageTest'));
        $this->assertTrue((bool)strpos($content, '@mixin AnnotatorPageTest_Extension'));
    }

    /**
     * Test the generation of annotations for an Extension
     */
    public function testAnnotateControllerExtension()
    {
        $classInfo = new AnnotateClassInfo('AnnotatorPageTest_Extension');
        $filePath  = $classInfo->getWritableClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->writeDocBlock($original, 'AnnotatorPageTest_Extension');

        $this->assertTrue((bool)strpos($annotated, '@property AnnotatorPageTest_Controller|AnnotatorPageTest_Extension $owner'));
    }
}
