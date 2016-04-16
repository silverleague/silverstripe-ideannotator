<?php

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator/Extensions
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

    /**
     * Keep track ot the annotation actions for extensions
     * An Extension can belong to many DataObjects.
     * This prevents that an Extension is ran twice on dev/build
     * @var array
     */
    public static $annotated_extensions = array();

    /**
     * Annotatable setup.
     * This is theoratically a constructor, but to save memory we're using setup called from {@see requireDefaultRecords}
     */
    public function setUp()
    {
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
        // Setup the protected values.
        $this->setUp();

        $skipAnnotation = null;

        // This is not the case on the command line
        if(Controller::has_curr()) {
            $skipAnnotation = Controller::curr()->getRequest()->getVar('skipannotation');
        }
        
        if ($skipAnnotation !== null || !$this->permissionChecker->environmentIsAllowed()) {
            return false;
        }

        $this->generateClassAnnotations();
        $this->generateControllerAnnotations();

        return true;
    }

    /**
     * Generate class own annotations
     */
    protected function generateClassAnnotations()
    {
        /* Annotate the current Class, if annotatable */
        $this->annotator->annotateObject($this->owner->ClassName);
        $this->generateExtensionAnnotations($this->owner->ClassName);
    }

    /**
     * Generate Page_Controller Annotations
     */
    protected function generateControllerAnnotations()
    {
        $reflector = new ReflectionClass($this->owner->ClassName);

        if($reflector->isSubclassOf('SiteTree')) {
            $controller = $this->owner->ClassName . '_Controller';
            if (class_exists($controller)) {
                $this->annotator->annotateObject($controller);
                $this->generateExtensionAnnotations($controller);
            }
        }
    }

    /**
     * Generate class Extension annotations
     */
    protected function generateExtensionAnnotations($className)
    {
        $extensions = (array)Config::inst()->get($className, 'extensions', Config::UNINHERITED);

        $extensions = array_diff($extensions, Annotatable::$annotated_extensions);

        if (!empty($extensions)) {
            foreach ($extensions as $extension) {
                $this->annotator->annotateObject(Annotatable::$annotated_extensions[$extension] = $extension);
            }
        }
    }
}
