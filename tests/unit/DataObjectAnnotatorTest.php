<?php

namespace SilverLeague\IDEAnnotator\Tests;

use PageController;
use PHPUnit_Framework_TestCase;
use RootTeam;
use SilverLeague\IDEAnnotator\Annotatable;
use SilverLeague\IDEAnnotator\AnnotateClassInfo;
use SilverLeague\IDEAnnotator\AnnotatePermissionChecker;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\Debug;
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
    protected function setUp()
    {
        parent::setUp();
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['silverleague/ideannotator']);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }

    public function testIsEnabled()
    {
        $this->assertTrue(DataObjectAnnotator::isEnabled());
    }

    /**
     * Test the expected classes show up in the Classes for Module
     */
    public function testGetClassesForModule()
    {
        $expectedClasses = [
            Team::class                             => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_Team.php',
            TeamChanged::class                      => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_TeamChanged.php',
            TeamComment::class                      => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_TeamComment.php',
            DocBlockMockWithDocBlock::class         => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DocBlockMockWithDocBlock.php',
            OtherDocBlockMockWithDocBlock::class    => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DocBlockMockWithDocBlock.php',
            DataObjectWithOldStyleTagMarkers::class => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DocBlockMockWithDocBlock.php',
            DoubleDataObjectInOneFile1::class       => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DoubleDataObjectInOneFile.php',
            DoubleDataObjectInOneFile2::class       => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DoubleDataObjectInOneFile.php',
            SubTeam::class                          => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_SubTeam.php',
            Player::class                           => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_Player.php',
            Team_Extension::class                   => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'DataObjectAnnotatorTest_Team_Extension.php',
            Annotatable::class                      => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Annotatable.php',
            AnnotatorPageTest_Extension::class      => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'AnnotatorPageTest.php',
            RootTeam::class                         => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'RootTeam.php',
            AnnotatorPageTest::class                => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'AnnotatorPageTest.php',
            AnnotatorPageTestController::class      => Director::baseFolder() . DIRECTORY_SEPARATOR . 'ideannotator' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock' . DIRECTORY_SEPARATOR . 'AnnotatorPageTest.php',
        ];
        $classes = $this->annotator->getClassesForModule('silverleague/ideannotator');

        $this->assertEquals($expectedClasses, $classes);
    }


    /**
     * As below, as we don't want to actively change the mocks, so enable mysite
     */
    public function testAnnotateObject()
    {
        $this->assertFalse($this->annotator->annotateObject(DataObject::class));

        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator', 'mysite']);
        $this->assertTrue($this->annotator->annotateObject(PageController::class));
    }

    /**
     * Not testing existing modules, as it wil actively alter the mock files, so enable mysite
     */
    public function testAnnotateModule()
    {
        $noModule = $this->annotator->annotateModule('');
        $this->assertFalse($noModule);
        $noModule = $this->annotator->annotateModule('mysite');
        $this->assertFalse($noModule);
        // Enable 'mysite' for testing
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator', 'mysite']);

        $module = $this->annotator->annotateModule('mysite');
        $this->assertTrue($module);
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
     * Test that root (non-namespaced) classes get annotated
     */
    public function testRootAnnotations()
    {
        $classInfo = new AnnotateClassInfo(RootTeam::class);
        $filePath = $classInfo->getClassFilePath();
        $run = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), RootTeam::class);
        $this->assertContains('@property string $Title', $run);
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
