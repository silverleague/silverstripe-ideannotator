<?php

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator
 *
 * @property DataObject|Annotatable $owner
 */
class Annotatable extends DataExtension
{

    /**
     * @var DataObjectAnnotator
     */
    protected $annotator;

    /**
     * @var AnnotatePermissionChecker
     */
    protected $permissionChecker;

    public function __construct() {
        parent::__construct();
        $this->annotator = Injector::inst()->get('DataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');

    }
    /**
     * This is the base function on which annotations are started.
     *
     * @todo rewrite this. It's not actually a requireDefaultRecords. But it's the only place to hook into the build-process to start the annotation process.
     * @return bool
     */
    public function requireDefaultRecords()
    {

        /** @var SS_HTTPRequest|NullHTTPRequest $request */
        $request = Controller::curr()->getRequest();
        $skipAnnotation = $request->getVar('skipannotation');
        if ($skipAnnotation !== null || !Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }

        $this->generateClassAnnotations();
        $this->generateExtensionAnnotations();

        return null;
    }

    /**
     * Generate class own annotations
     */
    private function generateClassAnnotations()
    {
        /* Annotate the current Class, if annotatable */
        if ($this->permissionChecker->classNameIsAllowed($this->owner->ClassName)) {
            $this->annotator->annotateDataObject($this->owner->ClassName);
            DB::alteration_message($this->owner->ClassName . ' Annotated', 'created');
        }
    }

    /**
     * Generate class Extension annotations
     */
    private function generateExtensionAnnotations()
    {
        /** @var array $extensions */
        $extensions = Config::inst()->get($this->owner->ClassName, 'extensions', Config::UNINHERITED);
        /* Annotate the extensions for this Class, if annotatable */
        if ($extensions) {
            foreach ($extensions as $extension) {
                if ($this->permissionChecker->classNameIsAllowed($extension)) {
                    $this->annotator->annotateDataObject($extension);
                    DB::alteration_message($extension . ' Annotated', 'created');
                }
            }
        }
    }
}
