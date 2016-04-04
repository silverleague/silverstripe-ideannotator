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
        $this->title = 'DataObject annotations for specific DataObjects';
        $this->description = "DataObject Annotator annotates your DO's if possible, helping you write better code.<br />"
            . 'Usage: add the module or DataObject as parameter to the URL, e.g. ?module=ideannotator<br />'
            . 'To undo annotations, add undo=true to the URL.';

    }

    /**
     * @param $request SS_HTTPRequest
     *
     * @return null
     */
    public function run($request)
    {
        if (!Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }

        $permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
        $className = $request->getVar('dataobject');
        $moduleName = $request->getVar('module');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        if ($className && $permissionChecker->classNameIsAllowed($className)) {
            $annotator->annotateDataObject($className);
        } elseif ($moduleName && $permissionChecker->moduleIsAllowed($moduleName)) {
            $annotator->annotateModule($moduleName);
        }

        $result = "Annotated module $moduleName/class $className\n";

        echo $result;
    }

}
