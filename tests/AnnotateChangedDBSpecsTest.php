<?php
use phpDocumentor\Reflection\DocBlock\Tag\MethodTag;

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
    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('Director', 'environment_type', 'dev');
        Config::inst()->update('DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        $this->annotator = Injector::inst()->get('MockDataObjectAnnotator');
    }

    public function testChangedDBSpecifications()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_TeamChanged');
        $filePath  = $classInfo->getWritableClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');
        $this->assertFalse(strpos($content, 'VisitCount') > 0);
    }

    public function testNonSupportedTagsWillNotBeTouched()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_TeamChanged');
        $filePath  = $classInfo->getWritableClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');
        $this->assertTrue(strpos($content, 'Simon') > 0);
    }

    public function testManuallyCommentedTagsWillNotBeRemoved()
    {

        Config::inst()->update('DataObjectAnnotatorTest_TeamChanged', 'extensions', array('DataObjectAnnotatorTest_Team_Extension'));

        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_TeamChanged');
        $filePath  = $classInfo->getWritableClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');

        $this->assertTrue(strpos($content, 'The Team Name') > 0);
        $this->assertTrue(strpos($content, 'This adds extra methods') > 0);
        $this->assertTrue(strpos($content, 'This is the Boss') > 0);
    }

}
