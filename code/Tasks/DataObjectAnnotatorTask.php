<?php

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
        $this->description = "DataObject Annotator annotates your DO's if possible, helping you write better code.<br />"
            . 'Usage: add the module or DataObject as parameter to the URL, e.g. ?module=mysite .';
    }

    /**
     * @param $request
     * @return bool
     */
    public function run($request)
    {
        /* @var $permissionChecker AnnotatePermissionChecker */
        $permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');

        if (!$permissionChecker->environmentIsAllowed()) {
            return false;
        }

        $className  = $request->getVar('object');
        $moduleName = $request->getVar('module');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();

        if ($className && $permissionChecker->classNameIsAllowed($className)) {
            $annotator->annotateObject($className);
        }

        if ($moduleName && $permissionChecker->moduleIsAllowed($moduleName)) {
            $annotator->annotateModule($moduleName);
        }

        return true;
    }

}
