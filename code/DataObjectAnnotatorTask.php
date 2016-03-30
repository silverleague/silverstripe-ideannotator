<?php

/**
 * Class DataObjectAnnotatorTask
 *
 * Task to add or remove annotations from a module or dataobject. 
 *
 * @package IDEAnnotator
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

        $permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
        $className = $request->getVar('dataobject');
        $moduleName = $request->getVar('module');
        $undo = $request->getVar('undo');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        if ($className && $permissionChecker->classNameIsAllowed($className)) {
            $annotator->annotateDataObject($className, $undo);
        } elseif ($moduleName && $permissionChecker->moduleIsAllowed($moduleName)) {
            $annotator->annotateModule($moduleName, $undo);
        }

        $result = (null !== $undo) ? "\nUndid annotating " : "\nAnnotated ";
        $result .= " module $moduleName/class $className\n";

        echo $result;
    }

}
