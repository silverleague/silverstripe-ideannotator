<?php

/**
 * Class DataObjectAnnotator
 * Generates phpdoc annotations for database fields and orm relations
 * so ide's with autocompletion and property inspection will recognize properties and relation methods.
 *
 * The annotations can be generated with dev/build with @see Annotatable
 * and from the @see DataObjectAnnotatorTask
 *
 * The generation is disabled by default.
 * It is advisable to only enable it in your local dev environment,
 * so the files won't change on a production server when you run dev/build
 */
class DataObjectAnnotator extends Object
{

    /**
     * This string marks the beginning of a generated annotations block
     */
    const STARTTAG = 'StartGeneratedWithDataObjectAnnotator';

    /**
     * This string marks the end of a generated annotations block
     */
    const ENDTAG = 'EndGeneratedWithDataObjectAnnotator';

    /**
     * @config
     * Enable generation from @see Annotatable and @see DataObjectAnnotatorTask
     */
    private static $enabled = false;

    /**
     * @config
     * Enable modules that are allowed to have generated docblocks for DataObjects and DataExtensions
     */
    private static $enabled_modules = array('mysite');

    /**
     * @param            $moduleName
     * @param bool|false $undo
     *
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     * @return false || void
     */
    public function annotateModule($moduleName, $undo = false)
    {
        if (!$this->moduleIsAllowed($moduleName)) {
            return false;
        }

        $classNames = ClassInfo::subclassesFor('DataObject');
        foreach ($classNames as $className) {
            $this->annotateDataObject($className, $undo);
        }

        $classNames = ClassInfo::subclassesFor('DataExtension');
        foreach ($classNames as $className) {
            $this->annotateDataObject($className, $undo);
        }
    }

    /**
     * @param            $className
     * @param bool|false $undo
     *
     * Generate docblock for a single subclass of DataObject or DataExtenions
     *
     * @return bool
     */
    public function annotateDataObject($className, $undo = false)
    {
        if (!$this->classNameIsAllowed($className)) {
            return false;
        }

        $filePath = $this->getClassFilePath($className);

        if (!$filePath) {
            return false;
        }

        if ($undo) {
            $this->removePHPDocBlock($filePath);
        } else {
            $original  = file_get_contents($filePath);
            $annotated = $this->getFileContentWithAnnotations($original, $className);
            // nothing has changed, no need to write to the file
            if ($annotated && $annotated != $original) {
                file_put_contents($filePath, $annotated);
            }
        }
    }

    /**
     * @param $className
     * Revert the file to its original state without the generated docblock from this module
     * @see removePHPDocBlock
     * @return bool
     */
    public function undoDataObject($className)
    {
        if (!$this->classNameIsAllowed($className)) {
            return false;
        }

        $filePath = $this->getClassFilePath($className);

        if (!$filePath) {
            return false;
        }

        $this->removePHPDocBlock($filePath);
    }

    /**
     * Performs the actual file writing
     * @param $filePath
     */
    protected function removePHPDocBlock($filePath)
    {
        $original = file_get_contents($filePath);
        $reverted = $this->getFileContentWithoutAnnotations($original);
        // nothing has changed, no need to write to the file
        if ($reverted && $reverted != $original) {
            file_put_contents($filePath, $reverted);
        }
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
    protected function classNameIsAllowed($className)
    {
        if (is_subclass_of($className, 'DataObject') || is_subclass_of($className, 'DataExtension')) {

            $filePath       = $this->getClassFilePath($className);
            $allowedModules = Config::inst()->get('DataObjectAnnotator', 'enabled_modules');

            foreach ($allowedModules as $moduleName) {
                $modulePath = BASE_PATH . DIRECTORY_SEPARATOR . $moduleName;
                if (substr($filePath, 0, strlen($modulePath)) == $modulePath) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $moduleName
     *
     * Check if a module is in the $allowed_modules array
     *
     * @return bool
     */
    protected function moduleIsAllowed($moduleName)
    {
        return in_array($moduleName, Config::inst()->get('DataObjectAnnotator', 'enabled_modules'));
    }

    /**
     * @param $className
     *
     * @return string
     */
    protected function getClassFilePath($className)
    {
        $reflector = new ReflectionClass($className);
        $filePath  = $reflector->getFileName();

        if (is_writable($filePath)) {
            return $filePath;
        }
    }

    /**
     * @param $fileContent
     * @param $className
     *
     * @return mixed|void
     */
    protected function getFileContentWithAnnotations($fileContent, $className)
    {
        $properties = $this->generateORMProperties($className);

        if (!$properties) {
            return;
        }

        $startTag = static::STARTTAG;
        $endTag   = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replacement = $startTag . "\n" . $properties . " * " . $endTag;
            return preg_replace("/$startTag([\s\S]*?)$endTag/", $replacement, $fileContent);
        } else {
            $classDeclaration = 'class ' . $className . ' extends'; // add extends to exclude Controller writes
            $properties       = "\n/**\n * " . $startTag . "\n"
                . $properties
                . " * " . $endTag . "\n"
                . " */\n\n$classDeclaration";
            return str_replace($classDeclaration, $properties, $fileContent);
        }
    }

    /**
     * @param $fileContent
     *
     * @return mixed
     */
    protected function getFileContentWithoutAnnotations($fileContent)
    {
        $startTag = static::STARTTAG;
        $endTag   = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replace = "/\n\/\*\*\n \* " . $startTag . "\n"
                . "([\s\S]*?)"
                . " \* $endTag"
                . "\n \*\/\n\n/";

            $fileContent = preg_replace($replace, "", $fileContent);
        }
        return $fileContent;
    }


    /**
     * @param $className DataObject || DataExtension
     *
     * @return string
     */
    protected function generateORMProperties($className)
    {
        $str = '';
        if ($fields = Config::inst()->get($className, 'db', Config::UNINHERITED)) {
            foreach ($fields as $k => $v) {
                $prop = 'string';

                $fieldObj = Object::create_from_string($v, $k);

                if (is_a($fieldObj, 'Int') || is_a($fieldObj, 'Boolean')) {
                    $prop = 'int';
                } elseif (is_a($fieldObj, 'Float') || is_a($fieldObj, 'Decimal')) {
                    $prop = 'float';
                }
                $str .= " * @property $prop $k\n";
            }
        }
        if ($fields = Config::inst()->get($className, 'has_one', Config::UNINHERITED)) {
            foreach ($fields as $k => $v) {
                $str .= " * @property int {$k}ID\n";
            }
            foreach ($fields as $k => $v) {
                $str .= " * @method $v $k\n";
            }
        }
        if ($fields = Config::inst()->get($className, 'has_many', Config::UNINHERITED)) {
            foreach ($fields as $k => $v) {
                $str .= " * @method DataList $k\n";
            }
        }
        if ($fields = Config::inst()->get($className, 'many_many', Config::UNINHERITED)) {
            foreach ($fields as $k => $v) {
                $str .= " * @method ManyManyList $k\n";
            }
        }
        if ($fields = Config::inst()->get($className, 'belongs_to', Config::UNINHERITED)) {
            foreach ($fields as $k => $v) {
                $str .= " * @method ManyManyList $k\n";
            }
        }
        if ($fields = Config::inst()->get($className, 'extensions', Config::UNINHERITED)) {
            foreach ($fields as $k) {
                $str .= " * @mixin $k\n";
            }
        }
        return $str;
    }
}
