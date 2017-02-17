<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

/**
 * Class DataObjectAnnotatorTest
 *
 * @mixin \PHPUnit_Framework_TestCase
 */
class AnnotatePermissionCheckerTest extends SapphireTest
{

    /**
     * @var \Axyr\IDEAnnotator\AnnotatePermissionChecker $permissionChecker
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
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'dev');
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled', true);
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('ideannotator'));

        Config::inst()->update('Axyr\IDEAnnotator\Tests\Team', 'extensions', array('Axyr\IDEAnnotator\Tests\Team_Extension'));

        $this->annotator = Injector::inst()->get('Axyr\IDEAnnotator\Tests\MockDataObjectAnnotator');
        $this->permissionChecker =  Injector::inst()->get('Axyr\IDEAnnotator\AnnotatePermissionChecker');
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->permissionChecker->isEnabled());

        Config::inst()->remove('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled');
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled', false);
        $this->assertFalse($this->permissionChecker->isEnabled());
    }

    public function testEnvironmentIsDev()
    {
        $this->assertTrue($this->permissionChecker->environmentIsDev());

        Config::inst()->remove('SilverStripe\Control\Director', 'environment_type');
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'live');
        $this->assertFalse($this->permissionChecker->environmentIsDev());


        Config::inst()->remove('SilverStripe\Control\Director', 'environment_type');
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'test');
        $this->assertFalse($this->permissionChecker->environmentIsDev());
    }

    public function testEnvironmentIsAllowed()
    {
        $this->assertTrue($this->permissionChecker->environmentIsAllowed());

        Config::inst()->remove('SilverStripe\Control\Director', 'environment_type');
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'test');
        $this->assertFalse($this->permissionChecker->environmentIsAllowed());

        Config::inst()->remove('SilverStripe\Control\Director', 'environment_type');
        Config::inst()->update('SilverStripe\Control\Director', 'environment_type', 'live');
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
        $this->assertTrue($this->permissionChecker->classNameIsAllowed('Axyr\IDEAnnotator\Tests\Team'));
        $this->assertTrue($this->permissionChecker->classNameIsAllowed('Axyr\IDEAnnotator\Tests\Team_Extension'));

        $this->assertFalse($this->permissionChecker->classNameIsAllowed('SilverStripe\ORM\DataObject'));
        $this->assertFalse($this->permissionChecker->classNameIsAllowed('SilverStripe\Assets\File'));

        Config::inst()->remove('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules');
        Config::inst()->update('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules', array('mysite'));

        $this->assertFalse($this->permissionChecker->classNameIsAllowed('Axyr\IDEAnnotator\Tests\Team'));
    }
}
