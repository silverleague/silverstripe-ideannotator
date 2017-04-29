<?php

namespace Axyr\IDEAnnotator;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator/Extensions
 *
 * @property \SilverStripe\Dev\DevBuildController $owner
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
        $this->annotator = Injector::inst()->get('Axyr\IDEAnnotator\DataObjectAnnotator');
        $this->permissionChecker = Injector::inst()->get('Axyr\IDEAnnotator\AnnotatePermissionChecker');
    }

    /**
     * Annotated Controllers and Extensions
     */
    public function afterCallActionHandler()
    {
        $this->setUp();

        $skipAnnotation = $this->owner->getRequest()->getVar('skipannotation');
        $envIsAllowed   = Director::get_environment_type() === 'dev';
        
        if ($skipAnnotation === null && $envIsAllowed) {

            $this->displayMessage(' Generating class docblocks');

            $modules = $this->permissionChecker->enabledModules();
            foreach ($modules as $module) {
                $this->annotator->annotateModule($module);
            }

            $this->displayMessage(' Docblock generation finished!');
        }
    }

    /**
     * @param $message
     */
    public function displayMessage($message)
    {
        echo Director::is_cli() ? "\n$message\n\n" : "<p><b>$message</b></p>";
    }

}
