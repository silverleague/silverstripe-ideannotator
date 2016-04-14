<?php


/**
 * Class DataObjectAnnotatorTest
 *
 * Several tests to make sure the Annotator does it's job correctly
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class DataObjectAnnotatorTest extends SapphireTest
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

        Config::inst()->update('DataObjectAnnotatorTest_Team', 'extensions',
            array('DataObjectAnnotatorTest_Team_Extension')
        );

        $this->annotator = Injector::inst()->get('MockDataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
    }

    /**
     * Test if the correct annotations are generated
     * for all database fields, relations and extensions
     * and that the start and end tags are present
     */
    public function testFileContentWithAnnotations()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team');
        $filePath  = $classInfo->getWritableClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_Team');

        $this->assertFalse((bool)strpos($content, DataObjectAnnotator::STARTTAG));
        $this->assertFalse((bool)strpos($content, DataObjectAnnotator::ENDTAG));

        // ClassName title
        $this->assertTrue((bool)strpos($content, ' * Class DataObjectAnnotatorTest_Team'));

        // database fields
        $this->assertTrue((bool)strpos($content, '@property string $Title'));
        $this->assertTrue((bool)strpos($content, '@property int $VisitCount'));
        $this->assertTrue((bool)strpos($content, '@property float $Price'));

        // has_one ID
        $this->assertTrue((bool)strpos($content, '@property int $CaptainID'));
        // has_one relation
        $this->assertTrue((bool)strpos($content, '@method DataObjectAnnotatorTest_Player Captain()'));
        // has_many relation
        $this->assertTrue((bool)strpos($content, '@method DataList|DataObjectAnnotatorTest_SubTeam[] SubTeams()'));
        // many_many relation
        $this->assertTrue((bool)strpos($content, '@method ManyManyList|DataObjectAnnotatorTest_Player[] Players()'));
        // DataExtension
        $this->assertTrue((bool)strpos($content, '@mixin DataObjectAnnotatorTest_Team_Extension'));
    }

    public function testExistingMethodsWillNotBeTagged()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team');
        $filePath  = $classInfo->getWritableClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_Team');
        $this->assertFalse((bool)strpos($content, '@method ManyManyList|DataObjectAnnotatorTest_SubTeam[] SecondarySubTeams()'));
    }

    /**
     * Test that multiple annotation runs won't generate ducplicate docblocks
     */
    public function testNothingHasChangedAfterSecondAnnotation()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team');
        $filePath  = $classInfo->getWritableClassFilePath();
        $original = file_get_contents($filePath);
        $firstRun = $this->annotator->getGeneratedFileContent($original, 'DataObjectAnnotatorTest_Team');
        $secondRun = $this->annotator->getGeneratedFileContent($firstRun, 'DataObjectAnnotatorTest_Team');
        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Test the generation of annotations for a DataExtension
     */
    public function testAnnotateDataExtension()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team_Extension');
        $filePath  = $classInfo->getWritableClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DataObjectAnnotatorTest_Team_Extension');

        $this->assertFalse((bool)strpos($annotated, DataObjectAnnotator::STARTTAG));
        $this->assertFalse((bool)strpos($annotated, DataObjectAnnotator::ENDTAG));
        $this->assertTrue((bool)strpos($annotated, '@property DataObjectAnnotatorTest_Team|DataObjectAnnotatorTest_Team_Extension $owner'));
        $this->assertTrue((bool)strpos($annotated, '@property string $ExtendedVarcharField'));
        $this->assertTrue((bool)strpos($annotated, '@property int $ExtendedIntField'));
        $this->assertTrue((bool)strpos($annotated, '@property int $ExtendedHasOneRelationshipID'));
        $this->assertTrue((bool)strpos($annotated, '@method DataObjectTest_Player ExtendedHasOneRelationship()'));
    }

    public function testRemoveOldStyleDocBlock()
    {
        $classInfo = new AnnotateClassInfo('DataObjectWithOldStyleTagMarkers');
        $filePath  = $classInfo->getWritableClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DataObjectWithOldStyleTagMarkers');
        $this->assertFalse((bool)strpos($annotated, DataObjectAnnotator::STARTTAG));
        $this->assertFalse((bool)strpos($annotated, DataObjectAnnotator::ENDTAG));

        $generator = new MockDocBlockGenerator('DataObjectWithOldStyleTagMarkers');
        $startAndEndTagsAreRemoved = $generator->removeOldStyleDocBlock($annotated);

        $this->assertFalse((bool)strpos($startAndEndTagsAreRemoved, DataObjectAnnotator::STARTTAG));
        $this->assertFalse((bool)strpos($startAndEndTagsAreRemoved, DataObjectAnnotator::ENDTAG));
    }

    public function testTwoClassesInOneFile()
    {
        $classInfo = new AnnotateClassInfo('DoubleDataObjectInOneFile1');
        $filePath  = $classInfo->getWritableClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DoubleDataObjectInOneFile1');

        $this->assertTrue((bool)strpos($annotated, '@property string $Title'));

        $annotated = $this->annotator->getGeneratedFileContent($annotated, 'DoubleDataObjectInOneFile2');

        $this->assertTrue((bool)strpos($annotated, '@property string $Name'));
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
    public function getGeneratedFileContent($fileContent, $className)
    {
        return parent::getGeneratedFileContent($fileContent, $className);
    }
}

class MockDocBlockGenerator extends DocBlockGenerator implements TestOnly
{
    public function removeOldStyleDocBlock($docBlock)
    {
        return parent::removeOldStyleDocBlock($docBlock);
    }
}
