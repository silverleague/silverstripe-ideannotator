<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * Class DataObjectAnnotatorTest
 *
 * @mixin PHPUnit_Framework_TestCase
 */
class AnnotatePermissionCheckerTest extends SapphireTest
{

    /**
     * @var AnnotatePermissionChecker $permissionChecker
     */
    private $permissionChecker = null;

    /**
     * @var MockDataObjectAnnotator
     */
    private $annotator;

    /**
     * Setup Defaults
     */
    public function setUp()
    {
        parent::setUp();
        Config::inst()->update('SilverStripe\\Control\\Director', 'environment_type', 'dev');
        Config::inst()->update('DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('DataObjectAnnotatorTest_Team', 'extensions',
            array('DataObjectAnnotatorTest_Team_Extension')
        );

        $this->annotator = Injector::inst()->get('MockDataObjectAnnotator');
        $this->permissionChecker =  Injector::inst()->get('AnnotatePermissionChecker');
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->permissionChecker->isEnabled());

        Config::inst()->remove('DataObjectAnnotator', 'enabled');
        Config::inst()->update('DataObjectAnnotator', 'enabled', false);
        $this->assertFalse($this->permissionChecker->isEnabled());
    }

    public function testEnvironmentIsDev()
    {
        $this->assertTrue($this->permissionChecker->environmentIsDev());

        Config::inst()->remove('SilverStripe\\Control\\Director', 'environment_type');
        Config::inst()->update('SilverStripe\\Control\\Director', 'environment_type', 'live');
        $this->assertFalse($this->permissionChecker->environmentIsDev());


        Config::inst()->remove('SilverStripe\\Control\\Director', 'environment_type');
        Config::inst()->update('SilverStripe\\Control\\Director', 'environment_type', 'test');
        $this->assertFalse($this->permissionChecker->environmentIsDev());
    }

    public function testEnvironmentIsAllowed()
    {
        $this->assertTrue($this->permissionChecker->environmentIsAllowed());

        Config::inst()->remove('SilverStripe\\Control\\Director', 'environment_type');
        Config::inst()->update('SilverStripe\\Control\\Director', 'environment_type', 'test');
        $this->assertFalse($this->permissionChecker->environmentIsAllowed());

        Config::inst()->remove('SilverStripe\\Control\\Director', 'environment_type');
        Config::inst()->update('SilverStripe\\Control\\Director', 'environment_type', 'live');
        $this->assertFalse($this->permissionChecker->environmentIsAllowed());
    }

    /**
     * Test is a module name is in the @Config enabled_modules
     * and will be seen as allowed or disallowed correctly
     */
    public function testModuleIsAllowed()
    {
        $this->assertFalse($this->permissionChecker->moduleIsAllowed('framework'));
        $this->assertTrue($this->permissionChecker->moduleIsAllowed('mysite'));
        $this->assertTrue($this->permissionChecker->moduleIsAllowed('ideannotator'));
    }

    /**
     * Test if a DataObject is in an allowed module name
     * and will be seen as allowed or disallowed correctly
     */
    public function testDataObjectIsAllowed()
    {
        $this->assertTrue($this->permissionChecker->classNameIsAllowed('DataObjectAnnotatorTest_Team'));
        $this->assertTrue($this->permissionChecker->classNameIsAllowed('DataObjectAnnotatorTest_Team_Extension'));

        $this->assertFalse($this->permissionChecker->classNameIsAllowed('SilverStripe\\ORM\\DataObject'));
        $this->assertFalse($this->permissionChecker->classNameIsAllowed('SilverStripe\\Assets\\File'));

        Config::inst()->remove('DataObjectAnnotator', 'enabled_modules');
        Config::inst()->update('DataObjectAnnotator', 'enabled_modules', array('mysite'));

        $this->assertFalse($this->permissionChecker->classNameIsAllowed('DataObjectAnnotatorTest_Team'));
    }
}
