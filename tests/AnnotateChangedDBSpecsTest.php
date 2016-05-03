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
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');
        $this->assertNotContains('VisitCount', $content);
    }

    public function testNonSupportedTagsWillNotBeTouched()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');
        $this->assertContains('Simon', $content);
    }

    public function testManuallyCommentedTagsWillNotBeRemoved()
    {

        Config::inst()->update('DataObjectAnnotatorTest_TeamChanged', 'extensions', array('DataObjectAnnotatorTest_Team_Extension'));

        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_TeamChanged');
        $filePath  = $classInfo->getClassFilePath();
        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_TeamChanged');

        $this->assertContains('The Team Name', $content);
        $this->assertContains('This adds extra methods', $content);
        $this->assertContains('This is the Boss', $content);
    }

}
