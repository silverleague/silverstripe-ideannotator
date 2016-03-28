<?php

/**
 * Class DataObjectAnnotatorTask
 *
 * Task to add or remove annotations from a module or dataobject
 */
class DataObjectAnnotatorTask extends BuildTask
{
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

        $permissionHelper = Injector::inst()->get('AnnotatePermissionChecker');
        $className = $request->getVar('dataobject');
        $moduleName = $request->getVar('module');
        $undo = $request->getVar('undo');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        if ($className && $permissionHelper->classNameIsAllowed($className)) {
            $annotator->annotateDataObject($className, $undo);
        } elseif ($moduleName && $permissionHelper->moduleIsAllowed($moduleName)) {
            $annotator->annotateModule($moduleName, $undo);
        }

        return null;
    }

}
