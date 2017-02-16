<?php

namespace IDEAnnotator\Tests;

use IDEAnnotator\AnnotateClassInfo;
use IDEAnnotator\AnnotatePermissionChecker;
use IDEAnnotator\DataObjectAnnotator;
use IDEAnnotator\DocBlockGenerator;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;


/**
 * Class DataObjectAnnotatorTest
 *
 * Several tests to make sure the Annotator does it's job correctly
 *
 * @mixin \PHPUnit_Framework_TestCase
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
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('IDEAnnotator\Tests\Team', 'extensions', array('IDEAnnotator\Tests\Team_Extension'));

        $this->annotator = Injector::inst()->get('IDEAnnotator\Tests\MockDataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('IDEAnnotator\AnnotatePermissionChecker');
    }

    /**
     * Test if the correct annotations are generated
     * for all database fields, relations and extensions
     * and that the start and end tags are present
     */
    public function testFileContentWithAnnotations()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\Team');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\Team');

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $content);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $content);

        // ClassName title
        $this->assertContains(' * Class \IDEAnnotator\Tests\Team', $content);

        // database fields
        $this->assertContains('@property string $Title', $content);
        $this->assertContains('@property int $VisitCount', $content);
        $this->assertContains('@property float $Price', $content);

        // has_one ID
        $this->assertContains('@property int $CaptainID', $content);
        // has_one relation
        $this->assertContains('@method \IDEAnnotator\Tests\Player Captain()', $content);
        // has_many relation
        $this->assertContains('@method \SilverStripe\ORM\DataList|\IDEAnnotator\Tests\SubTeam[] SubTeams()', $content);
        // many_many relation
        $this->assertContains('@method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\Player[] Players()', $content);
        $this->assertContains('@method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\Player[] Reserves()', $content);

        // DataExtension
        $this->assertContains('@mixin \IDEAnnotator\Tests\Team_Extension', $content);
    }

    public function testInversePlayerRelationOfTeam()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\Player');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\Player');

        $this->assertContains('@property boolean $IsRetired', $content);
        $this->assertContains('@property string $ShirtNumber', $content);
        $this->assertContains('@property int $FavouriteTeamID', $content);
        $this->assertContains('@method \IDEAnnotator\Tests\Team FavouriteTeam()', $content);

        $this->assertContains('@method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\Team[] TeamPlayer()', $content);
        $this->assertContains('@method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\Team[] TeamReserve()', $content);

    }

    public function testExistingMethodsWillNotBeTagged()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\Team');
        $filePath  = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), 'IDEAnnotator\Tests\Team');
        $this->assertNotContains('@method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\SubTeam[] SecondarySubTeams()', $content);
    }

    /**
     * Test that multiple annotation runs won't generate ducplicate docblocks
     */
    public function testNothingHasChangedAfterSecondAnnotation()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\Team');
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $firstRun = $this->annotator->getGeneratedFileContent($original, 'IDEAnnotator\Tests\Team');
        $secondRun = $this->annotator->getGeneratedFileContent($firstRun, 'IDEAnnotator\Tests\Team');
        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Test the generation of annotations for a DataExtension
     */
    public function testAnnotateDataExtension()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\Team_Extension');
        $filePath  = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'IDEAnnotator\Tests\Team_Extension');

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $annotated);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $annotated);
        $this->assertContains('@property \IDEAnnotator\Tests\Team|\IDEAnnotator\Tests\Team_Extension $owner', $annotated);
        $this->assertContains('@property string $ExtendedVarcharField', $annotated);
        $this->assertContains('@property int $ExtendedIntField', $annotated);
        $this->assertContains('@property int $ExtendedHasOneRelationshipID', $annotated);
        $this->assertContains('@method \IDEAnnotator\Tests\Player ExtendedHasOneRelationship()', $annotated);
    }

    public function testRemoveOldStyleDocBlock()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\DataObjectWithOldStyleTagMarkers');
        $filePath  = $classInfo->getClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'IDEAnnotator\Tests\DataObjectWithOldStyleTagMarkers');
        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $annotated);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $annotated);

        $generator = new MockDocBlockGenerator('IDEAnnotator\Tests\DataObjectWithOldStyleTagMarkers');
        $startAndEndTagsAreRemoved = $generator->removeOldStyleDocBlock($annotated);

        $this->assertNotContains(DataObjectAnnotator::STARTTAG, $startAndEndTagsAreRemoved);
        $this->assertNotContains(DataObjectAnnotator::ENDTAG, $startAndEndTagsAreRemoved);
    }

    public function testTwoClassesInOneFile()
    {
        $classInfo = new AnnotateClassInfo('IDEAnnotator\Tests\DoubleDataObjectInOneFile1');
        $filePath  = $classInfo->getClassFilePath();
        $original  = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, 'IDEAnnotator\Tests\DoubleDataObjectInOneFile1');

        $this->assertContains('@property string $Title', $annotated);

        $annotated = $this->annotator->getGeneratedFileContent($annotated, 'IDEAnnotator\Tests\DoubleDataObjectInOneFile2');

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
     * @return mixed
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
