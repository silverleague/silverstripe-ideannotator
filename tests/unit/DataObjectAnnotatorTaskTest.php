<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\DataObjectAnnotatorTask;
use SilverStripe\Dev\SapphireTest;

class DataObjectAnnotatorTaskTest extends SapphireTest
{
    public function testConstruct()
    {
        $task = DataObjectAnnotatorTask::create();
        $this->assertEquals('DataObject annotations for specific DataObjects, Extensions or Controllers', $task->getTitle());
        $this->assertEquals("DataObject Annotator annotates your DO's if possible, helping you write better code.<br />"
            . 'Usage: add the module or DataObject as parameter to the URL, e.g. ?module=mysite .', $task->getDescription());
    }
}
