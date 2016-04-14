<?php

/**
 * This test should fail, if a DB property is removed from
 * a class, but the property itself still exists after generation
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

}
