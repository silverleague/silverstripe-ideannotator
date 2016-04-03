<?php

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
        if(!Config::inst()->get('DataObjectAnnotator', 'enabled')) {
            return false;
        }

        // If the module is enabled, check for dev by config only

        // Copied from Director::isDev(), so we can bypass the session checking
        // Check config
        if(Config::inst()->get('Director', 'environment_type') === 'dev') return true;

        // Check if we are running on one of the test servers
        $devServers = (array)Config::inst()->get('Director', 'dev_servers');
        if(isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $devServers))  {
            return true;
        }

        return false;
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
        if (is_subclass_of($className, 'DataObject') || is_subclass_of($className, 'DataExtension')) {

            $classInfo = new AnnotateClassInfo($className);
            $filePath  = $classInfo->getWritableClassFilePath();

            $allowedModules = Config::inst()->get('DataObjectAnnotator', 'enabled_modules');

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
     * Check if a module is in the $allowed_modules array
     * Required for the buildTask.
     *
     * @param $moduleName
     *
     * @return bool
     */
    public function moduleIsAllowed($moduleName)
    {
        return in_array($moduleName, Config::inst()->get('DataObjectAnnotator', 'enabled_modules'), null);
    }
}
