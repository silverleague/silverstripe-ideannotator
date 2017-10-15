<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PHPUnit_Framework_TestCase;
use SilverLeague\IDEAnnotator\AnnotatePermissionChecker;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\AnnotateClassInfo;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

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
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator']);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }

    /**
     * Test if the correct annotations are generated
     * for all database fields, relations and extensions
     * and that the start and end tags are present
     */
    public function testFileContentWithAnnotations()
    {
        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Team::class);

        // ClassName title
        $this->assertContains(' * Class \SilverLeague\IDEAnnotator\Tests\Team', $content);

        // database fields
        $this->assertContains('@property string $Title', $content);
        $this->assertContains('@property int $VisitCount', $content);
        $this->assertContains('@property float $Price', $content);

        // has_one ID
        $this->assertContains('@property int $CaptainID', $content);
        // has_one relation
        $this->assertContains('@method \SilverLeague\IDEAnnotator\Tests\Player Captain()', $content);
        // has_many relation
        $this->assertContains(
            '@method \SilverStripe\ORM\DataList|\SilverLeague\IDEAnnotator\Tests\SubTeam[] SubTeams()',
            $content
        );
        // many_many relation
        $this->assertContains(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Player[] Players()',
            $content
        );
        $this->assertContains(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Player[] Reserves()',
            $content
        );

        // DataExtension
        $this->assertContains('@mixin \SilverLeague\IDEAnnotator\Tests\Team_Extension', $content);
    }

    public function testInversePlayerRelationOfTeam()
    {
        $classInfo = new AnnotateClassInfo(Player::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Player::class);

        $this->assertContains('@property boolean $IsRetired', $content);
        $this->assertContains('@property string $ShirtNumber', $content);
        $this->assertContains('@property int $FavouriteTeamID', $content);
        $this->assertContains('@method \SilverLeague\IDEAnnotator\Tests\Team FavouriteTeam()', $content);

        $this->assertContains(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Team[] TeamPlayer()',
            $content
        );
        $this->assertContains(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Team[] TeamReserve()',
            $content
        );
    }

    public function testExistingMethodsWillNotBeTagged()
    {
        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Team::class);
        $this->assertNotContains(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\SubTeam[] SecondarySubTeams()',
            $content
        );
    }

    /**
     * Test that multiple annotation runs won't generate ducplicate docblocks
     */
    public function testNothingHasChangedAfterSecondAnnotation()
    {
        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $firstRun = $this->annotator->getGeneratedFileContent($original, Team::class);
        $secondRun = $this->annotator->getGeneratedFileContent($firstRun, Team::class);
        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Test the generation of annotations for a DataExtension
     */
    public function testAnnotateDataExtension()
    {
        $classInfo = new AnnotateClassInfo(Team_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, Team_Extension::class);

        $this->assertContains(
            '@property \SilverLeague\IDEAnnotator\Tests\Team|\SilverLeague\IDEAnnotator\Tests\Team_Extension $owner',
            $annotated
        );
        $this->assertContains('@property string $ExtendedVarcharField', $annotated);
        $this->assertContains('@property int $ExtendedIntField', $annotated);
        $this->assertContains('@property int $ExtendedHasOneRelationshipID', $annotated);
        $this->assertContains(
            '@method \SilverLeague\IDEAnnotator\Tests\Player ExtendedHasOneRelationship()',
            $annotated
        );
    }

    /**
     *
     */
    public function testTwoClassesInOneFile()
    {
        $classInfo = new AnnotateClassInfo(DoubleDataObjectInOneFile1::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, DoubleDataObjectInOneFile1::class);

        $this->assertContains('@property string $Title', $annotated);

        $annotated = $this->annotator->getGeneratedFileContent($annotated, DoubleDataObjectInOneFile2::class);

        $this->assertContains('@property string $Name', $annotated);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
