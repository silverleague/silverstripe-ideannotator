<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Tasks\DataObjectAnnotatorTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class DataObjectAnnotatorTaskTest extends SapphireTest
{
    public function testConstruct()
    {
        $task = DataObjectAnnotatorTask::create();
        $this->assertEquals(
            'DataObject annotations for specific DataObjects, Extensions or Controllers',
            $task->getTitle()
        );
        $this->assertEquals(
            "DataObject Annotator annotates your DO's if possible, helping you write better code.<br />"
            . 'Usage: add the module or DataObject as parameter to the URL, e.g. ?module=mysite .',
            $task->getDescription()
        );
    }

    public function testRunFalse()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', false);

        $task = DataObjectAnnotatorTask::create();
        $request = new HTTPRequest('GET', '/dev/tasks/DataObjectAnnotatorTask');
        $this->assertFalse($task->run($request));
    }

    public function testRunTrue()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);

        $task = DataObjectAnnotatorTask::create();
        $request = new HTTPRequest('GET', '/dev/tasks/DataObjectAnnotatorTask');
        $this->assertTrue($task->run($request));
    }
}
