<?php

namespace SilverLeague\IDEAnnotator\Tasks;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DataObjectAnnotatorTask
 *
 * Task to add or remove annotations from a module or dataobject.
 *
 * @package IDEAnnotator/Tasks
 */
class DataObjectAnnotatorTask extends BuildTask
{

    protected string $title = 'DataObject annotations for specific DataObjects, Extensions or Controllers';

    protected static string $description = 'DataObject Annotator annotates your DO\'s if possible, helping you write better code.';


    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        /* @var $permissionChecker AnnotatePermissionChecker */
        $permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);

        if (!$permissionChecker->environmentIsAllowed()) {
            return Command::FAILURE;
        }

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        $module = $input->hasOption('module') ? $input->getOption('module') : null;
        $object = $input->hasOption('object') ? $input->getOption('object') : null;

        $annotator->annotateObject($object);

        $annotator->annotateModule($module);

        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('module', 'm', InputOption::VALUE_REQUIRED, 'annotate module'),
            new InputOption('object', 'o', InputOption::VALUE_REQUIRED, 'annotate a specific class'),
        ];
    }
}
