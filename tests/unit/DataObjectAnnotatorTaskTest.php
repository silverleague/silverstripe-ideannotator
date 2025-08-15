<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Tasks\DataObjectAnnotatorTask;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DataObjectAnnotatorTaskTest extends SapphireTest
{
    /**
     * @var BufferedOutput
     */
    protected $bufferedOutput;

    /**
     * @var PolyOutput
     */
    protected $mockOutput;

    public function testRunFalse()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', false);

        $task = DataObjectAnnotatorTask::create();
        $this->assertEquals($task->run(new ArrayInput([]), $this->mockOutput), Command::FAILURE);
    }

    public function testRunTrue()
    {
        Config::modify()->set(DataObjectAnnotator::class, 'enabled', true);

        $task = DataObjectAnnotatorTask::create();
        $this->assertEquals($task->run(new ArrayInput([]), $this->mockOutput), Command::SUCCESS);
    }

    protected function setUp(): void
    {
        parent::setUp();

        ob_start();

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
