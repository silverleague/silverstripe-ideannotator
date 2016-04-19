<?php

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator/Extensions
 *
 * @property DevBuildController $owner
 */
class Annotatable extends Extension
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
     * Annotated Controllers and Extensions
     */
    public function afterCallActionHandler()
    {
        $this->setUp();

        $skipAnnotation = $this->owner->getRequest()->getVar('skipannotation');

        if ($skipAnnotation === null || $this->permissionChecker->environmentIsAllowed()) {

            $this->displayMessage('Generating class docblocks');

            $modules = $this->permissionChecker->enabledModules();
            foreach ($modules as $module) {
                $this->annotator->annotateModule($module);
            }

            $this->displayMessage('Docblock generation finished!');
        }
    }

    /**
     * @param $message
     * @return string
     */
    public function displayMessage($message)
    {
        return Director::is_cli() ? "\n$message\n\n" : "<p><b>$message</b></p>";
    }

}
