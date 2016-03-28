<?php


/**
 * Class DataObjectAnnotatorTest
 */
class DataObjectAnnotatorTest extends SapphireTest
{

    /**
     * @var MockDataObjectAnnotator
     */
    private $annotator = null;

    /**
     * @var AnnotatePermissionChecker $annotatorHelper
     */
    private $annotatorHelper = null;

    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('DataObjectAnnotatorTest_Team', 'extensions',
            array('DataObjectAnnotatorTest_Team_Extension')
        );

        $this->annotator = MockDataObjectAnnotator::create();
        $this->annotatorHelper = new AnnotatePermissionChecker();
    }

    /**
     * Test is a module name is in the @Config enabled_modules
     * and will be seen as allowed or disallowed correctly
     */
    public function testModuleIsAllowed()
    {
        $this->assertFalse($this->annotatorHelper->moduleIsAllowed('framework'));
        $this->assertTrue($this->annotatorHelper->moduleIsAllowed('mysite'));
        $this->assertTrue($this->annotatorHelper->moduleIsAllowed('ideannotator'));
    }

    /**
     * Test if a DataObject is in an allowed module name
     * and will be seen as allowed or disallowed correctly
     */
    public function testDataObjectIsAllowed()
    {
        $this->assertTrue($this->annotatorHelper->classNameIsAllowed('DataObjectAnnotatorTest_Team'));
        $this->assertTrue($this->annotatorHelper->classNameIsAllowed('DataObjectAnnotatorTest_Team_Extension'));

        $this->assertFalse($this->annotatorHelper->classNameIsAllowed('DataObject'));
        $this->assertFalse($this->annotatorHelper->classNameIsAllowed('File'));

        Config::inst()->remove('DataObjectAnnotator', 'enabled_modules');
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('mysite'));

        $this->assertFalse($this->annotatorHelper->classNameIsAllowed('DataObjectAnnotatorTest_Team'));
    }

    /**
     * Test if the correct annotations are generated
     * for all database fields, relations and extensions
     * and that the start and end tags are present
     */
    public function testFileContentWithAnnotations()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team');
        $content = $this->annotator->getFileContentWithAnnotations(file_get_contents($filePath),
            'DataObjectAnnotatorTest_Team');

        $this->assertTrue((bool)strpos($content, DataObjectAnnotator::STARTTAG));
        $this->assertTrue((bool)strpos($content, DataObjectAnnotator::ENDTAG));
        // database fields
        $this->assertTrue((bool)strpos($content, '@property string Title'));
        $this->assertTrue((bool)strpos($content, '@property int VisitCount'));
        // has_one ID
        $this->assertTrue((bool)strpos($content, '@property int CaptainID'));
        // had_one relation
        $this->assertTrue((bool)strpos($content, '@method DataObjectAnnotatorTest_Player Captain'));
        // has_many relation
        $this->assertTrue((bool)strpos($content, '@method DataList|DataObjectAnnotatorTest_SubTeam[] SubTeams'));
        // many_many relation
        $this->assertTrue((bool)strpos($content, '@method ManyManyList|DataObjectAnnotatorTest_Player[] Players'));
        // DataExtension
        $this->assertTrue((bool)strpos($content, '@mixin DataObjectAnnotatorTest_Team_Extension'));
    }

    /**
     * Test if there are no generated annotations present
     */
    public function testFileContentWithoutAnnotations()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team');
        $content = $this->annotator->getFileContentWithoutAnnotations(file_get_contents($filePath));

        $this->assertFalse(strpos($content, DataObjectAnnotator::STARTTAG));
        $this->assertFalse(strpos($content, DataObjectAnnotator::ENDTAG));
        $this->assertFalse(strpos($content, '@property string Title'));
        $this->assertFalse(strpos($content, '@property int VisitCount'));
        $this->assertFalse(strpos($content, '@property int CaptainID'));
        $this->assertFalse(strpos($content, '@method DataObjectAnnotatorTest_Player Captain'));
        $this->assertFalse(strpos($content, '@method DataList|DataObjectAnnotatorTest_SubTeam[] SubTeams'));
        $this->assertFalse(strpos($content, '@method ManyManyList|DataObjectAnnotatorTest_Player[] Players'));
        $this->assertFalse(strpos($content, '@mixin DataObjectAnnotatorTest_Team_Extension'));
    }

    /**
     * Test if an undo action will lead to the same file as the original file
     */
    public function testFileIsTheSameAfterUndoAnnotate()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team');
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getFileContentWithAnnotations($original, 'DataObjectAnnotatorTest_Team');
        $undone = $this->annotator->getFileContentWithoutAnnotations($annotated);

        $this->assertEquals($original, $undone);
    }

    /**
     * Test that multiple annotation runs won't generate ducplicate docblocks
     */
    public function testNothingHasChangedAfterSecondAnnotation()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team');
        $original = file_get_contents($filePath);
        $firstRun = $this->annotator->getFileContentWithAnnotations($original, 'DataObjectAnnotatorTest_Team');
        $secondRun = $this->annotator->getFileContentWithAnnotations($firstRun, 'DataObjectAnnotatorTest_Team');
        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Test that nothing has changed after running the getWithoutAnnotations
     */
    public function testNothingHasChangedAfterSecondWithoutAnnotation()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team');
        $original = file_get_contents($filePath);
        $firstRun = $this->annotator->getFileContentWithoutAnnotations($original);
        $secondRun = $this->annotator->getFileContentWithoutAnnotations($firstRun);
        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Test the generation of annotations for a DataExtension
     */
    public function testAnnotateDataExtension()
    {
        $filePath = $this->annotatorHelper->getClassFilePath('DataObjectAnnotatorTest_Team_Extension');
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getFileContentWithAnnotations($original, 'DataObjectAnnotatorTest_Team_Extension');

        $this->assertTrue((bool)strpos($annotated, DataObjectAnnotator::STARTTAG));
        $this->assertTrue((bool)strpos($annotated, DataObjectAnnotator::ENDTAG));
        $this->assertTrue((bool)strpos($annotated, '@property DataObjectAnnotatorTest_Team|DataObjectAnnotatorTest_Team_Extension owner'));
        $this->assertTrue((bool)strpos($annotated, '@property string ExtendedVarcharField'));
        $this->assertTrue((bool)strpos($annotated, '@property int ExtendedIntField'));
        $this->assertTrue((bool)strpos($annotated, '@property int ExtendedHasOneRelationshipID'));
        $this->assertTrue((bool)strpos($annotated, '@method DataObjectTest_Player ExtendedHasOneRelationship'));
    }
}

/**
 * Class MockDataObjectAnnotator
 * Overload DataObjectAnnotator to make protected methods testable.
 * In this way we can just test the generated annotations without actually writing the files.
 */
class MockDataObjectAnnotator extends DataObjectAnnotator implements TestOnly
{

    /**
     * @param $fileContent
     * @param $className
     *
     * @return mixed|void
     */
    public function getFileContentWithAnnotations($fileContent, $className)
    {
        return parent::getFileContentWithAnnotations($fileContent, $className);
    }

    /**
     * @param $fileContent
     *
     * @return mixed
     */
    public function getFileContentWithoutAnnotations($fileContent)
    {
        return parent::getFileContentWithoutAnnotations($fileContent);
    }

}
