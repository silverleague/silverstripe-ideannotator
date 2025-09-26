<?php

namespace SilverLeague\IDEAnnotator\Helpers;

use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleManifest;
use SilverStripe\ORM\DataObject;

/**
 * Class AnnotatePermissionChecker
 *
 * Helperclass to check if the current environment, class or module is allowed to be annotated.
 * This is abstracted from @see DataObjectAnnotator to separate and clean up.
 *
 * @package IDEAnnotator/Helpers
 */
class AnnotatePermissionChecker
{

    /**
     * In the future we will support other Classes as well.
     * We list the core classes, but in fact only it's subclasses are supported
     * @see AnnotatePermissionChecker::classNameIsSupported();
     */
    protected $supportedParentClasses = [
        DataObject::class,
        Extension::class,
        Controller::class,
        Extension::class
    ];

    /**
     * @return bool
     */
    public function environmentIsAllowed()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return Director::isDev();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)DataObjectAnnotator::config()->get('enabled');
    }

    /**
     * @return array
     */
    public function getSupportedParentClasses()
    {
        return $this->supportedParentClasses;
    }

    /**
     * Check if a DataObject or DataExtension subclass is allowed by checking if the file
     * is in the $allowed_modules array
     * The permission is checked by matching the filePath and modulePath
     *
     * @param $className
     *
     * @return bool
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function classNameIsAllowed($className)
    {
        if ($this->classNameIsSupported($className)) {
            $classInfo = new AnnotateClassInfo($className);
            $filePath = $classInfo->getClassFilePath();
            $module = Injector::inst()->createWithArgs(
                ModuleManifest::class,
                [Director::baseFolder()]
            )->getModuleByPath($filePath);

            $allowedModules = (array)DataObjectAnnotator::config()->get('enabled_modules');

            return in_array($module->getName(), $allowedModules, true);
        }

        return false;
    }

    /**
     * Check if a (subclass of ) class is a supported
     *
     * @param $className
     * @return bool
     */
    public function classNameIsSupported($className)
    {
        foreach ($this->supportedParentClasses as $supportedParent) {
            if (is_subclass_of($className, $supportedParent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a module is in the $allowed_modules array
     * Required for the buildTask.
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function moduleIsAllowed($moduleName)
    {
        return in_array($moduleName, $this->enabledModules(), false);
    }

    /**
     * @return array
     */
    public function enabledModules()
    {
        $enabled = (array)DataObjectAnnotator::config()->get('enabled_modules');

        // modules might be enabled more then once.
        return array_combine($enabled, $enabled);
    }
}
