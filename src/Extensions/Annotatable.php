<?php

namespace SilverLeague\IDEAnnotator\Extensions;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\PolyExecution\PolyOutput;

/**
 * Class Annotatable
 *
 * Annotate extension for the provided DataObjects for autocompletion purposes.
 * Start annotation, if skipannotation is not set and the annotator is enabled.
 *
 * @package IDEAnnotator/Extensions
 * @property DevBuildController|Annotatable $owner
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
     * @config
     * Enables generation of docblocks on build
     *
     * @var bool
     */
    private static $annotate_on_build = true;

    /**
     * Annotated Controllers and Extensions
     * @param PolyOutput $output Output pipe for the task
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function onAfterBuild(PolyOutput $output)
    {
        if ($this->owner->config()->annotate_on_build) {
            $this->annotateModules($output);
        }
    }

    /**
     * Conditionally annotate this project's modules if enabled and not skipped
     * @param PolyOutput $output Output pipe for the task
     * @return bool Return true if annotation was successful
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function annotateModules(PolyOutput $output)
    {
        $envIsAllowed = Director::isDev() && DataObjectAnnotator::config()->get('enabled');

        // Only instatiate things when we want to run it, this is for when the module is accidentally installed
        // on non-dev environments for example
        if ($envIsAllowed) {
            $this->setUp();

            $output->writeln(['<options=bold>Generating class docblocks</>', '']);
            $output->startList(PolyOutput::LIST_UNORDERED);

            $modules = $this->permissionChecker->enabledModules();
            foreach ($modules as $module) {
                $this->annotator->annotateModule($module);
            }

            $output->stopList();

            $output->writeln(['<options=bold>Docblock generation finished!</>', '']);

            return true;
        }

        return false;
    }

    /**
     * Annotatable setup.
     * This is theoretically a constructor, but to save memory we're using setup
     * called from {@see onAfterBuild}
     * @throws NotFoundExceptionInterface
     */
    public function setUp()
    {
        $this->annotator = Injector::inst()->get(DataObjectAnnotator::class);
        $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
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
