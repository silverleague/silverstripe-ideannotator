<?php


class DataObjectAnnotatorTask extends BuildTask
{
    /**
     * @param $request SS_HTTPRequest
     *
     * @return bool || void
     */
    public function run($request)
    {
        if (!Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }

        $className  = $request->getVar('dataobject');
        $moduleName = $request->getVar('module');
        $undo       = $request->getVar('undo');

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        if($className) {
            $annotator->annotateDataObject($className, $undo);
        }elseif($moduleName) {
            $annotator->annotateModule($moduleName, $undo);
        }
    }
}
