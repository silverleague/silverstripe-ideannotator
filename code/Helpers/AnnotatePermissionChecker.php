<?php

/**
 * Class AnnotatePermissionChecker
 *
 * Helper to check if the class called can be annotated
 */
class AnnotatePermissionChecker
{
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

            $filePath = $this->getClassFilePath($className);
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

    /**
     * @param $className
     *
     * @return string
     */
    public function getClassFilePath($className)
    {
        $reflector = new ReflectionClass($className);
        $filePath = $reflector->getFileName();

        if (is_writable($filePath)) {
            return $filePath;
        }

        return false;
    }

}
