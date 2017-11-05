<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\Annotatable;
use SilverLeague\IDEAnnotator\AnnotatePermissionChecker;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class AnnotatableTest extends SapphireTest
{

    /**
     * @var Annotatable
     */
    protected $extension;

    protected function setUp()
    {
        parent::setUp();
        $this->extension = Injector::inst()->get(Annotatable::class);
    }

    public function testOutput()
    {
        $this->extension->displayMessage('Hello world');
        $this->expectOutputString("\nHello world\n\n");
    }

    public function testSetUp()
    {
        $this->extension->setUp();
        $this->assertInstanceOf(DataObjectAnnotator::class, $this->extension->getAnnotator());
        $this->assertInstanceOf(AnnotatePermissionChecker::class, $this->extension->getPermissionChecker());
    }
}
