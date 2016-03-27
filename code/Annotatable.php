<?php

/**
 * Class Annotatable
 *
 * Annotate the provided DataObjects for autocompletion purposes.
 */
class Annotatable extends DataExtension
{
    /**
     * This is the base function on which annotations are started.
     *
     * @todo rewrite this. It's not actually a requireDefaultRecords. But it's the only place to hook into the build-process to start the annotation process.
     * @return bool
     */
    public function requireDefaultRecords()
    {
        $skipAnnotation = filter_input(INPUT_GET, 'skipannotation');
        if ($skipAnnotation !== null || !Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }

        /* @var $annotator DataObjectAnnotator */
        $annotator = DataObjectAnnotator::create();
        $annotator->annotateDataObject($this->owner->ClassName);

        if ($extensions = Config::inst()->get($this->owner->ClassName, 'extensions', Config::UNINHERITED)) {
            foreach ($extensions as $extension) {
                $annotator->annotateDataObject($extension);
            }
        }

        return null;
    }
}
