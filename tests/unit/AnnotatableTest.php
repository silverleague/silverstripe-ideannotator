<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Extensions\Annotatable;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class AnnotatableTest extends SapphireTest
{

    /**
     * @var Annotatable
     */
    protected $extension;

    public function testSetUp()
    {
        $this->extension->setUp();
        $this->assertInstanceOf(DataObjectAnnotator::class, $this->extension->getAnnotator());
        $this->assertInstanceOf(AnnotatePermissionChecker::class, $this->extension->getPermissionChecker());
    }

    public function testOutput()
    {
        $this->extension->displayMessage('Hello world');
        $this->expectOutputString("\nHello world\n\n");
    }

    public function testDisplayEndMessage()
    {
        $this->extension->displayMessage('Hello world', true, true);
        $this->expectOutputString("\nHELLO WORLD");
    }

    public function testDisplayHeaderMessage()
    {
        $this->extension->displayMessage('Hello world', true);
        $this->expectOutputString("\nHELLO WORLD\n\n");
    }

    public function testAfterCallActionHandlerRequestBlock()
    {
        $request = new HTTPRequest('GET', '/dev/build', ['skipannotation' => true]);
        $this->extension->getOwner()->setRequest($request);

        $this->assertFalse($this->extension->afterCallActionHandler());
    }

    public function testAfterCallActionHandlerConfigBlock()
    {
        DataObjectAnnotator::config()->set('enabled', false);

        $this->assertFalse($this->extension->afterCallActionHandler());
    }

    public function testAfterCallActionHandlerDevBlock()
    {
        Environment::setEnv('SS_ENVIRONMENT_TYPE', 'Live');

        $this->assertFalse($this->extension->afterCallActionHandler());
    }

    public function testAfterCallActionHandler()
    {
        $request = new HTTPRequest('GET', '/dev/build');
        $this->extension->getOwner()->setRequest($request);
        DataObjectAnnotator::config()->set('enabled', true);
        DataObjectAnnotator::config()->set('enabled_modules', ['mysite', 'app']);
        $this->assertTrue($this->extension->afterCallActionHandler());
    }

    public function testAfterCallActionHandlerRun()
    {
        $request = new HTTPRequest('GET', '/dev/build');
        $this->extension->getOwner()->setRequest($request);
        DataObjectAnnotator::config()->set('enabled', true);
        DataObjectAnnotator::config()->set('enabled_modules', ['mysite', 'app']);

        $this->extension->afterCallActionHandler();

        $output = $this->getActualOutput();
        $this->assertContains("GENERATING CLASS DOCBLOCKS", $output);
        $this->assertContains("+ Page Annotated", $output);
        $this->assertContains("DOCBLOCK GENERATION FINISHED!", $output);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->extension = Injector::inst()->get(Annotatable::class);
        $this->extension->setOwner(Controller::create());
    }
}
