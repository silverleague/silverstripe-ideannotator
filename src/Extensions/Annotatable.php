<?php

namespace SilverLeague\IDEAnnotator;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

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
     * Keep track ot the annotation actions for extensions
     * An Extension can belong to many DataObjects.
     * This prevents that an Extension is ran twice on dev/build
     * @var array
     */
    public static $annotated_extensions = [];
    /**
     * @var DataObjectAnnotator
     */
    protected $annotator;
    /**
     * @var AnnotatePermissionChecker
     */
    protected $permissionChecker;

    /**
     * Annotated Controllers and Extensions
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function afterCallActionHandler()
    {
        $this->setUp();

        $skipAnnotation = $this->owner->getRequest()->getVar('skipannotation');
        $envIsAllowed = Director::isDev() && Config::inst()->get(DataObjectAnnotator::class, 'enabled');

        if ($skipAnnotation === null && $envIsAllowed) {
            $this->displayMessage("<div class='build'><p><b>Generating class docblocks</b></p><ul>\n\n");

            $modules = $this->permissionChecker->enabledModules();
            foreach ($modules as $module) {
                $this->annotator->annotateModule($module);
            }

            $this->displayMessage("</ul>\n<p><b>Docblock generation finished!</b></p></div>");
        }
    }

    /**
     * Annotatable setup.
     * This is theoratically a constructor, but to save memory we're using setup called from {@see requireDefaultRecords}
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setUp()
    {
        $this->annotator = Injector::inst()->get(DataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }

    /**
     * @param $message
     */
    public function displayMessage($message)
    {
        echo Director::is_cli() ? "\n" . $message . "\n\n" : "<p><b>$message</b></p>";
    }
}
