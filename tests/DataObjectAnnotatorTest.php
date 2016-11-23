<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;


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
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_Team');

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $content);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $content);

        // ClassName title
        $this->assertContains(' * Class DataObjectAnnotatorTest_Team', $content);

        // database fields
        $this->assertContains('@property string $Title', $content);
        $this->assertContains('@property int $VisitCount', $content);
        $this->assertContains('@property float $Price', $content);

        // has_one ID
        $this->assertContains('@property int $CaptainID', $content);
        // has_one relation
        $this->assertContains('@method DataObjectAnnotatorTest_Player Captain()', $content);
        // has_many relation
        $this->assertContains('@method DataList|DataObjectAnnotatorTest_SubTeam[] SubTeams()', $content);
        // many_many relation
        $this->assertContains('@method ManyManyList|DataObjectAnnotatorTest_Player[] Players()', $content);
        $this->assertContains('@method ManyManyList|DataObjectAnnotatorTest_Player[] Reserves()', $content);

        // DataExtension
        $this->assertContains('@mixin DataObjectAnnotatorTest_Team_Extension', $content);
    }

    public function testInversePlayerRelationOfTeam()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Player');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_Player');

        $this->assertContains('@property boolean $IsRetired', $content);
        $this->assertContains('@property string $ShirtNumber', $content);
        $this->assertContains('@property int $FavouriteTeamID', $content);
        $this->assertContains('@method DataObjectAnnotatorTest_Team FavouriteTeam()', $content);

        $this->assertContains('@method ManyManyList|DataObjectAnnotatorTest_Team[] TeamPlayer()', $content);
        $this->assertContains('@method ManyManyList|DataObjectAnnotatorTest_Team[] TeamReserve()', $content);

    }

    public function testExistingMethodsWillNotBeTagged()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'DataObjectAnnotatorTest_Team');
        $this->assertNotContains('@method ManyManyList|DataObjectAnnotatorTest_SubTeam[] SecondarySubTeams()', $content);
    }

    /**
     * Test that multiple annotation runs won't generate ducplicate docblocks
     */
    public function testNothingHasChangedAfterSecondAnnotation()
    {
        $classInfo = new AnnotateClassInfo('DataObjectAnnotatorTest_Team');
        $filePath  = $classInfo->getClassFilePath();
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
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DataObjectAnnotatorTest_Team_Extension');

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $annotated);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $annotated);
        $this->assertContains('@property DataObjectAnnotatorTest_Team|DataObjectAnnotatorTest_Team_Extension $owner', $annotated);
        $this->assertContains('@property string $ExtendedVarcharField', $annotated);
        $this->assertContains('@property int $ExtendedIntField', $annotated);
        $this->assertContains('@property int $ExtendedHasOneRelationshipID', $annotated);
        $this->assertContains('@method DataObjectTest_Player ExtendedHasOneRelationship()', $annotated);
    }

    public function testRemoveOldStyleDocBlock()
    {
        $classInfo = new AnnotateClassInfo('DataObjectWithOldStyleTagMarkers');
        $filePath  = $classInfo->getClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DataObjectWithOldStyleTagMarkers');
        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $annotated);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $annotated);

        $generator = new MockDocBlockGenerator('DataObjectWithOldStyleTagMarkers');
        $startAndEndTagsAreRemoved = $generator->removeOldStyleDocBlock($annotated);

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $startAndEndTagsAreRemoved);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $startAndEndTagsAreRemoved);
    }

    public function testTwoClassesInOneFile()
    {
        $classInfo = new AnnotateClassInfo('DoubleDataObjectInOneFile1');
        $filePath  = $classInfo->getClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'DoubleDataObjectInOneFile1');

        $this->assertContains('@property string $Title', $annotated);

        $annotated = $this->annotator->getGeneratedFileContent($annotated, 'DoubleDataObjectInOneFile2');

        $this->assertContains('@property string $Name', $annotated);
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
