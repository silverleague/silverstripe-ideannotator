<?php

/**
 * Class AnnotatePermissionChecker
 *
 * Helperclass to check if the current environment, class or module is allowed to be annotated.
 * This is abstracted from @see DataObjectAnnotator to separate and clean up.
 *
 * @package IDEAnnotator/Helpers
 */
class AnnotatePermissionChecker extends Object
{


    /**
     * @config
     * Enable generation from @see Annotatable and @see DataObjectAnnotatorTask
     * @var bool
     */
    private static $enabled = false;

    /**
     * @config
     * Enable modules that are allowed to have generated docblocks for DataObjects and DataExtensions
     * @var array
     */
    private static $enabled_modules = array('mysite');

    /**
     * In the future we will support other Classes as well.
     * We list the core classes, but in fact only it's subclasses are supported
     *
     * @see AnnotatePermissionChecker::classNameIsSupported();
     */
    protected $supportedParentClasses = array(
        'DataObject',
        'DataExtension'
    );

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
     */
    public function environmentIsAllowed()
    {
        // Not enabled, so skip anyway
        if (!static::isEnabled()) {
            return false;
        }

        // If the module is enabled, check for dev by config only

        // Copied from Director::isDev(), so we can bypass the session checking
        // Check config
        if (Config::inst()->get('Director', 'environment_type') === 'dev') {
            return true;
        }

        // Check if we are running on one of the test servers
        $devServers = (array)Config::inst()->get('Director', 'dev_servers');
        $httpHost = Controller::curr()->getRequest()->getHeader('Host');

        return (null !== $httpHost && in_array($httpHost, $devServers, null));
    }


    /**
     * Check if a DataObject or DataExtension subclass is allowed by checking if the file
     * is in the $allowed_modules array
     * The permission is checked by matching the filePath and modulePath
     *
     * @param string $className
     *
     * @return bool
     */
    public function classNameIsAllowed($className)
    {
        if ($this->classNameIsSupported($className)) {

            $classInfo = new AnnotateClassInfo($className);
            $filePath = $classInfo->getWritableClassFilePath();

            $allowedModules = static::config()->get('enabled_modules');

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
     * @param DataObject|string $className
     *
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
     * @param $moduleName
     *
     * @return bool
     */
    public function moduleIsAllowed($moduleName)
    {
        return in_array($moduleName, static::config()->get('enabled_modules'), null);
    }


    /**
     * @return boolean
     */
    public static function isEnabled()
    {
        return static::config()->get('enabled');
    }

    /**
     * @param boolean $enabled
     */
    public static function setEnabled($enabled)
    {
        static::config()->enabled = $enabled;
    }

    /**
     * @return array
     */
    public static function getEnabledModules()
    {
        return static::config()->get('enabled_modules');
    }

    /**
     * @param array $enabled_modules
     */
    public static function setEnabledModules($enabled_modules)
    {
        static::config()->enabled_modules = $enabled_modules;
    }

}
