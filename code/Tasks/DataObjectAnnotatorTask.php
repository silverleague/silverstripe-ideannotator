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
     * The annotations here are explicitly called by an admin.
     * Thus, we allow annotations and don't check for permissions?
     * @param $request SS_HTTPRequest
     *
     * @return null
     */
    public function run($request)
    {
        $className = $request->getVar('dataobject');
        $moduleName = $request->getVar('module');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        if ($className) {
            $annotator->annotateDataObject($className);
        } elseif ($moduleName) {
            $annotator->annotateModule($moduleName);
        }

        $result = "Annotated module $moduleName/class $className\n";

        echo $result;
    }

}
