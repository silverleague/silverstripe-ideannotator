<?php

namespace SilverLeague\IDEAnnotator\Tasks;

use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

/**
 * Class DataObjectAnnotatorTask
 *
 * Task to add or remove annotations from a module or dataobject.
 *
 * @package IDEAnnotator/Tasks
 */
class DataObjectAnnotatorTask extends BuildTask
{

    /**
     * DataObjectAnnotatorTask constructor.
     * Setup default values. In this case title and description.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = 'DataObject annotations for specific DataObjects, Extensions or Controllers';

        $this->description = 'DataObject Annotator annotates your DO\'s if possible,' .
            ' helping you write better code.' .
            '<br />Usage: add the module or DataObject as parameter to the URL,' .
            ' e.g. ?module=mysite';
    }

    /**
     * @param HTTPRequest $request
     * @return bool
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function run($request)
    {
        /* @var $permissionChecker AnnotatePermissionChecker */
        $permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);

        if (!$permissionChecker->environmentIsAllowed()) {
            return false;
        }

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();

        $annotator->annotateObject($request->getVar('object'));

        $annotator->annotateModule($request->getVar('module'));

        return true;
    }
}
