<?php

/**
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 * Class Annotatable
 *
 * @property DataObject|Annotatable owner
 */
class Annotatable extends DataExtension
{
    /**
     * @return bool|null
     */
    public function requireDefaultRecords()
    {
        $permissionChecker = new AnnotatePermissionChecker();

        /** @var SS_HTTPRequest|NullHTTPRequest $request */
        $request = Controller::curr()->getRequest();
        $skipAnnotation = $request->getVar('skipannotation');
        if ($skipAnnotation !== null || !Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }
        $annotator = DataObjectAnnotator::create();
        /* Annotate the current Class, if annotatable */
        if ($permissionChecker->classNameIsAllowed($this->owner->ClassName)) {
            /* @var $annotator DataObjectAnnotator */
            $annotator->annotateDataObject($this->owner->ClassName);
        }
        /** @var array $extensions */
        $extensions = Config::inst()->get($this->owner->ClassName, 'extensions', Config::UNINHERITED);
        /* Annotate the extensions for this Class, if annotatable */
        if ($extensions) {
            foreach ($extensions as $extension) {
                if ($permissionChecker->classNameIsAllowed($extension)) {
                    $annotator->annotateDataObject($extension);
                }
            }
        }

        return null;
    }
}
