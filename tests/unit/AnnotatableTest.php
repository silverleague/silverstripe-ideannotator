<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Extensions\Annotatable;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\BaseKernel;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Kernel;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class AnnotatableTest extends SapphireTest
{

    /**
     * @var Annotatable
     */
    protected $extension;

    /**
     * @var BufferedOutput
     */
    protected $bufferedOutput;

    /**
     * @var PolyOutput
     */
    protected $mockOutput;

    public function testSetUp()
    {
        $this->extension->setUp();
        $this->assertInstanceOf(DataObjectAnnotator::class, $this->extension->getAnnotator());
        $this->assertInstanceOf(AnnotatePermissionChecker::class, $this->extension->getPermissionChecker());
    }

    public function testAfterCallActionHandlerConfigBlock()
    {
        DataObjectAnnotator::config()->set('enabled', false);

        $this->assertFalse($this->extension->annotateModules($this->mockOutput));
    }

    public function testAfterCallActionHandlerDevBlock()
    {
        Injector::inst()->get(Kernel::class)->setEnvironment(BaseKernel::LIVE);

        $this->assertFalse($this->extension->annotateModules($this->mockOutput));
    }

    public function testAfterCallActionHandler()
    {
        $request = new HTTPRequest('GET', '/dev/build');
        $this->extension->getOwner()->setRequest($request);
        DataObjectAnnotator::config()->set('enabled', true);
        DataObjectAnnotator::config()->set('enabled_modules', ['mysite', 'app']);
        $this->assertTrue($this->extension->annotateModules($this->mockOutput));
    }

    public function testAfterCallActionHandlerRun()
    {
        $request = new HTTPRequest('GET', '/dev/build');
        $this->extension->getOwner()->setRequest($request);
        DataObjectAnnotator::config()->set('enabled', true);
        DataObjectAnnotator::config()->set('enabled_modules', ['mysite', 'app']);
        $this->extension->getOwner()->config()->set('annotate_on_build', true);

        $this->extension->onAfterBuild($this->mockOutput);

        $polyOutput = $this->bufferedOutput->fetch();
        $output = $this->getActualOutput();
        $this->assertStringContainsString("Generating class docblocks", $polyOutput);
        $this->assertStringContainsString("+ Page", $output);
        $this->assertStringContainsString("Docblock generation finished!", $polyOutput);
    }

    public function testAfterCallActionHandlerRunNoAnnotate()
    {
        $request = new HTTPRequest('GET', '/dev/build');
        $this->extension->getOwner()->setRequest($request);
        DataObjectAnnotator::config()->set('enabled', true);
        DataObjectAnnotator::config()->set('enabled_modules', ['mysite', 'app']);
        $this->extension->getOwner()->config()->set('annotate_on_build', false);

        $this->extension->onAfterBuild($this->mockOutput);

        $output = $this->bufferedOutput->fetch();
        $this->assertStringNotContainsString("Generating class docblocks", $output);
    }

    protected function setUp(): void
    {
        parent::setUp();

        ob_start();

        $this->extension = Injector::inst()->get(Annotatable::class);
        $this->extension->setOwner(Controller::create());

        $this->bufferedOutput = new BufferedOutput();
        $this->mockOutput = PolyOutput::create(PolyOutput::FORMAT_ANSI, OutputInterface::VERBOSITY_NORMAL, true, $this->bufferedOutput);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ob_end_clean();
    }

    /**
     * Gets the current content in the output buffer
     * @return string|false
     */
    public function getActualOutput()
    {
        return ob_get_contents();
    }
}
