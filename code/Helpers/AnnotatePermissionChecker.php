<?php

namespace Axyr\IDEAnnotator;

use SilverStripe\Core\Config\Config;

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
    protected $supportedParentClasses = array(
        'SilverStripe\ORM\DataObject',
        'SilverStripe\ORM\DataExtension',
        'SilverStripe\Control\Controller',
        'SilverStripe\Core\Extension'
    );

    /**
     * @return bool
     */
    public function environmentIsAllowed()
    {
        if(!$this->isEnabled()) return false;

        return $this->environmentIsDev();
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
     */
    public function classNameIsAllowed($className)
    {
        if ($this->classNameIsSupported($className)) {

            $classInfo = new AnnotateClassInfo($className);
            $filePath  = $classInfo->getClassFilePath();

            $allowedModules = (array)Config::inst()->get('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules');

            foreach ($allowedModules as $moduleName) {
                $modulePath = BASE_PATH . DIRECTORY_SEPARATOR . $moduleName;
                if (0 === strpos($filePath, $modulePath)) {
                    return true;
                }
            }
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
            if(is_subclass_of($className, $supportedParent)) {
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
        return in_array($moduleName, $this->enabledModules(), null);
    }

    /**
     * @return array
     */
    public function enabledModules()
    {
        $enabled = (array)Config::inst()->get('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled_modules');

        // modules might be enabled more then once.
        return array_combine($enabled, $enabled);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)Config::inst()->get('Axyr\IDEAnnotator\DataObjectAnnotator', 'enabled');
    }

    /**
     * Since we are changing php files, generation of docblocks should never be done on a live server.
     * We can't prevent this, but we should make it as hard as possible.
     *
     * Generation is only allowed when :
     * - The module is enabled
     * - The site is in dev mode by configuration
     *
     * This means we will not change files if the ?isDev=1 $_GET variable is used to put a live site into dev mode.
     * This also means we can't use Director::isDev();
     *
     * @return bool
     */
    public function environmentIsDev()
    {
        $devServers = (array)Config::inst()->get('SilverStripe\Control\Director', 'dev_servers');

        return Config::inst()->get('SilverStripe\Control\Director', 'environment_type') === 'dev'
            || (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $devServers));
    }
}
