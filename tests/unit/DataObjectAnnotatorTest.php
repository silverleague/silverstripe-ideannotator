<?php

namespace SilverLeague\IDEAnnotator\Tests;

use Page;
use PageController;
use PHPUnit_Framework_TestCase;
use RootTeam;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Extensions\Annotatable;
use SilverLeague\IDEAnnotator\Generators\OrmTagGenerator;
use SilverLeague\IDEAnnotator\Helpers\AnnotateClassInfo;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleManifest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;

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
     * Are we enabled?
     */
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
            Team::class                          => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_Team.php'),
            TeamChanged::class                   => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_TeamChanged.php'),
            TeamComment::class                   => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_TeamComment.php'),
            DocBlockMockWithDocBlock::class      => realpath(__DIR__ . '/../mock/DocBlockMockWithDocBlock.php'),
            OtherDocBlockMockWithDocBlock::class => realpath(__DIR__ . '/../mock/DocBlockMockWithDocBlock.php'),
            DoubleDataObjectInOneFile1::class    => realpath(__DIR__ . '/../mock/DoubleDataObjectInOneFile.php'),
            DoubleDataObjectInOneFile2::class    => realpath(__DIR__ . '/../mock/DoubleDataObjectInOneFile.php'),
            SubTeam::class                       => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_SubTeam.php'),
            Player::class                        => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_Player.php'),
            Team_Extension::class                => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_Team_Extension.php'),
            Annotatable::class                   => realpath(__DIR__ . '/../../src/Extensions/Annotatable.php'),
            TestAnnotatorPage_Extension::class   => realpath(__DIR__ . '/../mock/TestAnnotatorPage.php'),
            RootTeam::class                      => realpath(__DIR__ . '/../mock/RootTeam.php'),
            TestAnnotatorPage::class             => realpath(__DIR__ . '/../mock/TestAnnotatorPage.php'),
            TestAnnotatorPageController::class   => realpath(__DIR__ . '/../mock/TestAnnotatorPage.php'),
            TeamSupporter::class                 => realpath(__DIR__ . '/../mock/DataObjectAnnotatorTest_TeamSupporter.php'),
        ];
        $classes = $this->annotator->getClassesForModule('silverleague/ideannotator');
        // Sort the array, so we don't get accidental errors due to manual ordering
        ksort($expectedClasses);
        ksort($classes);
        $this->assertEquals($expectedClasses, $classes);
    }

    /**
     * As below, as we don't want to actively change the mocks, so enable mysite
     */
    public function testAnnotateObject()
    {
        $this->assertFalse($this->annotator->annotateObject(DataObject::class));

        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['ideannotator', 'mysite', 'app']);
        $this->assertTrue($this->annotator->annotateObject(PageController::class));
    }

    /**
     * Not testing existing modules, as it wil actively alter the mock files, so enable mysite
     */
    public function testAnnotateModule()
    {
        $noModule = $this->annotator->annotateModule('');
        $this->assertFalse($noModule);
        $projectName = ModuleManifest::config()->get('project');
        if (!$projectName) {
            $projectName = 'app';
        }
        $noModule = $this->annotator->annotateModule($projectName);
        $this->assertFalse($noModule);
        // Enable 'mysite' (or 'app') for testing
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', [$projectName]);

        $module = $this->annotator->annotateModule($projectName);
        $this->assertTrue($module, "$projectName was not allowed");
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

        $type = OrmTagGenerator::defaultType();

        // ClassName title
        $this->assertStringContainsString(' * Class \SilverLeague\IDEAnnotator\Tests\Team', $content);

        // database fields
        $this->assertStringContainsString('@property ' . $type . ' $Title', $content);
        $this->assertStringContainsString('@property int $VisitCount', $content);
        $this->assertStringContainsString('@property float $Price', $content);
        $this->assertStringContainsString('@property ' . $type . ' $Dude', $content);
        $this->assertStringContainsString('@property ' . $type . ' $Dudette', $content);

        // has_one ID
        $this->assertStringContainsString('@property int $CaptainID', $content);
        // has_one relation
        $this->assertStringContainsString('@method \SilverLeague\IDEAnnotator\Tests\Player Captain()', $content);
        // has_many relation
        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\DataList|\SilverLeague\IDEAnnotator\Tests\SubTeam[] SubTeams()',
            $content
        );
        // many_many relation
        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Player[] Players()',
            $content
        );
        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Player[] Reserves()',
            $content
        );
        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\TeamSupporter[] Supporters()',
            $content
        );

        // DataExtension
        $this->assertStringContainsString('@mixin \SilverLeague\IDEAnnotator\Tests\Team_Extension', $content);
    }

    /**
     * Test if the correct annotations are generated
     * for all database fields, relations and extensions
     * and that the start and end tags are present
     */
    public function testShortFileContentWithAnnotations()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);

        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Team::class);

        $type = OrmTagGenerator::defaultType();

        // database fields
        $this->assertStringContainsString('@property ' . $type . ' $Title', $content);
        $this->assertStringContainsString('@property int $VisitCount', $content);
        $this->assertStringContainsString('@property float $Price', $content);

        // has_one ID
        $this->assertStringContainsString('@property int $CaptainID', $content);
        // has_one relation
        $this->assertStringContainsString('@method Player Captain()', $content);
        // has_many relation
        $this->assertStringContainsString(
            '@method DataList|SubTeam[] SubTeams()',
            $content
        );
        // many_many relation
        $this->assertStringContainsString(
            '@method ManyManyList|Player[] Players()',
            $content
        );
        $this->assertStringContainsString(
            '@method ManyManyList|Player[] Reserves()',
            $content
        );
        $this->assertStringContainsString(
            '@method ManyManyList|TeamSupporter[] Supporters()',
            $content
        );

        // DataExtension
        $this->assertStringContainsString('@mixin Team_Extension', $content);
    }

    public function testInversePlayerRelationOfTeam()
    {
        $classInfo = new AnnotateClassInfo(Player::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Player::class);

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString('@property bool $IsRetired', $content);
        $this->assertStringContainsString('@property ' . $type . ' $ShirtNumber', $content);
        $this->assertStringContainsString('@property ' . $type . ' $Shirt', $content);
        $this->assertStringContainsString('@property int $FavouriteTeamID', $content);
        $this->assertStringContainsString('@method \SilverLeague\IDEAnnotator\Tests\Team CaptainTeam()', $content);
        $this->assertStringContainsString('@method \SilverLeague\IDEAnnotator\Tests\Team FavouriteTeam()', $content);
        $this->assertStringContainsString('@property string $OtherObjectClass', $content);
        $this->assertStringContainsString('@property int $OtherObjectID', $content);
        $this->assertStringContainsString('@method \SilverStripe\ORM\DataObject OtherObject()', $content);

        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Team[] TeamPlayer()',
            $content
        );
        $this->assertStringContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Team[] TeamReserve()',
            $content
        );
    }

    public function testDefaults()
    {
        $count = 0;
        DataObjectAnnotator::pushExtensionClass(Page::class);
        foreach (DataObjectAnnotator::getExtensionClasses() as $class) {
            if ($class === 'Page') {
                $count++;
            }
        }
        $this->assertEquals(1, $count);
    }

    public function testSetExtensionClasses()
    {
        $expected = [
            'SilverLeague\IDEAnnotator\Tests\TestAnnotatorPageController',
            'SilverLeague\IDEAnnotator\Tests\Team',
            'SilverStripe\Admin\LeftAndMain',
            'SilverStripe\Admin\ModalController',
            // 'SilverStripe\Assets\File',
            // 'SilverStripe\AssetAdmin\Forms\FileFormFactory',
            // 'SilverStripe\Assets\Shortcodes\FileShortcodeProvider',
            'SilverStripe\CMS\Controllers\ContentController',
            'SilverStripe\CMS\Controllers\ModelAsController',
            'SilverStripe\CMS\Model\SiteTree',
            'SilverStripe\Control\Controller',
            'SilverStripe\Dev\DevBuildController',
            'SilverStripe\Forms\Form',
            'SilverStripe\ORM\DataObject',
            'SilverStripe\Security\Group',
            'SilverStripe\Security\Member',
            'SilverStripe\Forms\GridField\GridFieldDetailForm',
            'SilverStripe\Forms\GridField\GridFieldPrintButton',
            // 'SilverStripe\ORM\FieldType\DBField',
        ];

        // Instantiate - triggers extension class list generation
        DataObjectAnnotator::create();
        $result = DataObjectAnnotator::getExtensionClasses();
        foreach ($expected as $expectedClass) {
            $this->assertContains($expectedClass, $result, "Classes are: " . json_encode($result));
        }
    }

    public function testShortInversePlayerRelationOfTeam()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);

        $classInfo = new AnnotateClassInfo(Player::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Player::class);

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString('@property bool $IsRetired', $content);
        $this->assertStringContainsString('@property ' . $type . ' $ShirtNumber', $content);
        $this->assertStringContainsString('@property int $FavouriteTeamID', $content);
        $this->assertStringContainsString('@method Team FavouriteTeam()', $content);
        $this->assertStringContainsString('@property string $OtherObjectClass', $content);
        $this->assertStringContainsString('@property int $OtherObjectID', $content);
        $this->assertStringContainsString('@method DataObject OtherObject()', $content);

        $this->assertStringContainsString(
            '@method ManyManyList|Team[] TeamPlayer()',
            $content
        );
        $this->assertStringContainsString(
            '@method ManyManyList|Team[] TeamReserve()',
            $content
        );
    }

    public function testExistingMethodsWillNotBeTagged()
    {
        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Team::class);
        $this->assertStringNotContainsString(
            '@method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\SubTeam[] SecondarySubTeams()',
            $content
        );
    }

    public function testShortExistingMethodsWillNotBeTagged()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);

        $classInfo = new AnnotateClassInfo(Team::class);
        $filePath = $classInfo->getClassFilePath();

        $content = $this->annotator->getGeneratedFileContent(file_get_contents($filePath), Team::class);
        $this->assertStringNotContainsString(
            '@method ManyManyList|SubTeam[] SecondarySubTeams()',
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

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString('@property ' . $type . ' $Title', $run);
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

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString(
            '@property \SilverLeague\IDEAnnotator\Tests\Team|\SilverLeague\IDEAnnotator\Tests\Team_Extension $owner',
            $annotated
        );
        $this->assertStringContainsString('@property ' . $type . ' $ExtendedVarcharField', $annotated);
        $this->assertStringContainsString('@property int $ExtendedIntField', $annotated);
        $this->assertStringContainsString('@property int $ExtendedHasOneRelationshipID', $annotated);
        $this->assertStringContainsString(
            '@method \SilverLeague\IDEAnnotator\Tests\Player ExtendedHasOneRelationship()',
            $annotated
        );
    }

    /**
     * Test the generation of annotations for a DataExtension
     */
    public function testShortAnnotateDataExtension()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', true);

        $classInfo = new AnnotateClassInfo(Team_Extension::class);
        $filePath = $classInfo->getClassFilePath();
        $original = file_get_contents($filePath);
        $annotated = $this->annotator->getGeneratedFileContent($original, Team_Extension::class);

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString(
            '@property Team|Team_Extension $owner',
            $annotated
        );
        $this->assertStringContainsString('@property ' . $type . ' $ExtendedVarcharField', $annotated);
        $this->assertStringContainsString('@property int $ExtendedIntField', $annotated);
        $this->assertStringContainsString('@property int $ExtendedHasOneRelationshipID', $annotated);
        $this->assertStringContainsString(
            '@method Player ExtendedHasOneRelationship()',
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

        $type = OrmTagGenerator::defaultType();

        $this->assertStringContainsString('@property ' . $type . ' $Title', $annotated);

        $annotated = $this->annotator->getGeneratedFileContent($annotated, DoubleDataObjectInOneFile2::class);

        $this->assertStringContainsString('@property ' . $type . ' $Name', $annotated);
    }

    /**
     * Setup Defaults
     */
    protected function setUp(): void
    {
        parent::setUp();
        Config::modify()->set(DataObjectAnnotator::class, 'use_short_name', false);

        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);
        Config::modify()->set(DataObjectAnnotator::class, 'enabled_modules', ['silverleague/ideannotator']);

        $this->annotator = Injector::inst()->get(MockDataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }
}
