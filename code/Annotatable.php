<?php


class Annotatable extends DataExtension
{
    public function requireDefaultRecords()
    {
        if (!Config::inst()->get('DataObjectAnnotator', 'enabled') || isset($_GET['skipannotation'])) {
            return false;
        }

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        $annotator->annotateDataObject($this->owner->ClassName);

        if ($extensions = Config::inst()->get($this->owner->ClassName, 'extensions', Config::UNINHERITED)) {
            foreach($extensions as $extension) {
                $annotator->annotateDataObject($extension);
            }
        }
    }
}
