<?php

/**
 * Class Annotatable
 *
 * Annotate the provided DataObjects for autocompletion purposes.
 */
class Annotatable extends DataExtension
{
    /**
     * @todo rewrite this. It's not actually a requireDefaultRecords. But it's the only place to hook into the build-process to start the annotation process.
     * @return bool
     */
    public function requireDefaultRecords()
    {
        $skipAnnotation = filter_input(INPUT_GET, 'skipannotation');
        if (!Config::inst()->get('DataObjectAnnotator', 'enabled') || $skipAnnotation !== null) {
            return false;
        }

        print_r($this->owner->Classname . "\n");

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        $annotator->annotateDataObject($this->owner->ClassName);

        if ($extensions = Config::inst()->get($this->owner->ClassName, 'extensions', Config::UNINHERITED)) {
            foreach ($extensions as $extension) {
                $annotator->annotateDataObject($extension);
            }
        }
    }
}
