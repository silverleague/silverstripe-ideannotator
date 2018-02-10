<?php

namespace SilverLeague\IDEAnnotator\Extensions;

use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator/Extensions
 * @property \SilverStripe\Dev\DevBuildController|\SilverLeague\IDEAnnotator\Annotatable $owner
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
     * @return bool // Return a boolean for test-purposes
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function afterCallActionHandler()
    {
        $envIsAllowed = Director::isDev() && DataObjectAnnotator::config()->get('enabled');
        $skipAnnotation = $this->owner->getRequest()->getVar('skipannotation');

        // Only instatiate things when we want to run it, this is for when the module is accidentally installed
        // on non-dev environments for example
        if ($skipAnnotation === null && $envIsAllowed) {
            $this->setUp();

            $this->displayMessage('Generating class docblocks', true, false);

            $modules = $this->permissionChecker->enabledModules();
            foreach ($modules as $module) {
                $this->annotator->annotateModule($module);
            }

            $this->displayMessage('Docblock generation finished!', true, true);

            return true;
        }

        return false;
    }

    /**
     * Annotatable setup.
     * This is theoretically a constructor, but to save memory we're using setup
     * called from {@see afterCallActionHandler}
     * @throws NotFoundExceptionInterface
     */
    public function setUp()
    {
        $this->annotator = Injector::inst()->get(DataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
    }

    /**
     * @param string $message
     * @param bool $heading
     * @param bool $end
     */
    public function displayMessage($message, $heading = false, $end = false)
    {
        if ($heading) {
            if (!$end) {
                echo Director::is_cli() ?
                    strtoupper("\n$message\n\n") :
                    "<div class='build'><p><b>$message</b><ul>";
            } else {
                echo Director::is_cli() ? strtoupper("\n" . $message) : "</ul><p><b>$message</b></b></div>";
            }
        } else {
            echo Director::is_cli() ? "\n$message\n\n" : "<li>$message</li>";
        }
    }

    /**
     * @return DataObjectAnnotator
     */
    public function getAnnotator()
    {
        return $this->annotator;
    }

    /**
     * @return AnnotatePermissionChecker
     */
    public function getPermissionChecker()
    {
        return $this->permissionChecker;
    }
}
