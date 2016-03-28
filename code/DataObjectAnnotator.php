<?php

/**
 * Class DataObjectAnnotator
 * Generates phpdoc annotations for database fields and orm relations
 * so IDE's with autocompletion and property inspection will recognize properties and relation methods.
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
     * @var array
     * Available properties to generate docblocks for.
     */
    protected static $propertyTypes = array(
        'Owner',
        'DB',
        'HasOne',
        'BelongsTo',
        'HasMany',
        'ManyMany',
        'BelongsManyMany',
        'Extensions',
    );

    protected $annotatePermissionChecker;

    /**
     * All classes that subclass DataObject
     * @var array
     */
    protected $classes;

    /**
     * All classes that subclass Object
     * @var array
     */
    protected $extensionClasses;

    /**
     * List of all objects, so we can find the extensions.
     *
     * @var array
     */
    protected $dataExtensions;

    /**
     * @var string
     * Overall string for dataset.
     */
    protected $resultString = '';

    public function __construct()
    {
        $this->classes = ClassInfo::subclassesFor('DataObject');
        $this->extensionClasses = ClassInfo::subclassesFor('Object');
        $this->dataExtensions = ClassInfo::subclassesFor('DataExtension');
        $this->annotatePermissionChecker = new AnnotatePermissionChecker();
    }

    /**
     * @param string $moduleName
     * @param bool|false $undo
     *
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     * @return false || void
     */
    public function annotateModule($moduleName, $undo = false)
    {
        if (!$this->annotatePermissionChecker->moduleIsAllowed($moduleName)) {
            return false;
        }

        foreach ($this->classes as $className) {
            $this->annotateDataObject($className, $undo);
            $this->resultString = ''; // Reset the result after each class
        }

        foreach ($this->dataExtensions as $className) {
            $this->annotateDataObject($className, $undo);
            $this->resultString = '';
        }

        return null;
    }

    /**
     * @param string     $className
     * @param bool|false $undo
     *
     * Generate docblock for a single subclass of DataObject or DataExtenions
     *
     * @return bool
     */
    public function annotateDataObject($className, $undo = false)
    {
        if (!$this->annotatePermissionChecker->classNameIsAllowed($className)) {
            return false;
        }

        $filePath = $this->annotatePermissionChecker->getClassFilePath($className);

        if (!$filePath) {
            return false;
        }

        if ($undo) {
            $this->removePHPDocBlock($filePath);
        } else {
            $original = file_get_contents($filePath);
            $annotated = $this->getFileContentWithAnnotations($original, $className);
            // nothing has changed, no need to write to the file
            if ($annotated && $annotated !== $original) {
                file_put_contents($filePath, $annotated);
            }
        }

        return null;
    }

    /**
     * Revert the file to its original state without the generated docblock from this module
     *
     * @param $className
     *
     * @see removePHPDocBlock
     * @return bool
     */
    public function undoDataObject($className)
    {
        if (!$this->annotatePermissionChecker->classNameIsAllowed($className)) {
            return false;
        }

        $filePath = $this->annotatePermissionChecker->getClassFilePath($className);

        if (!$filePath) {
            return false;
        }

        $this->removePHPDocBlock($filePath);

        return null;
    }

    /**
     * Performs the actual file writing
     *
     * @param $filePath
     */
    private function removePHPDocBlock($filePath)
    {
        $original = file_get_contents($filePath);
        $reverted = $this->getFileContentWithoutAnnotations($original);
        // nothing has changed, no need to write to the file
        if ($reverted && $reverted !== $original) {
            file_put_contents($filePath, $reverted);
        }
    }

    /**
     * Get the file and have the ORM Properties generated.
     *
     * @param String $fileContent
     * @param String $className
     *
     * @return mixed|void
     */
    protected function getFileContentWithAnnotations($fileContent, $className)
    {
        $this->generateORMProperties($className);

        if (!$this->resultString) {
            return null;
        }

        $startTag = static::STARTTAG;
        $endTag = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replacement = $startTag . "\n * \n" . $this->resultString . " * \n * " . $endTag;

            return preg_replace("/$startTag([\s\S]*?)$endTag/", $replacement, $fileContent);
        } else {
            $classDeclaration = 'class ' . $className . ' extends'; // add extends to exclude Controller writes
            $properties = "\n/**\n * " . $startTag . "\n * \n"
                . $this->resultString
                . " * \n *  \n * " . $endTag . "\n"
                . " */\n$classDeclaration";

            return str_replace($classDeclaration, $properties, $fileContent);
        }
    }

    /**
     * Get the literal contents of the DataObject file.
     *
     * @param $fileContent
     *
     * @return mixed
     */
    protected function getFileContentWithoutAnnotations($fileContent)
    {
        $startTag = static::STARTTAG;
        $endTag = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replace = "/\n\/\*\* \n \* " . $startTag . "\n"
                . "([\s\S]*?)"
                . " \* $endTag"
                . "\n \*\/\n/";

            $fileContent = preg_replace($replace, '', $fileContent);
        }

        return $fileContent;
    }


    /**
     * @param String $className
     *
     * @return string
     */
    protected function generateORMProperties($className)
    {
        /*
         * Start with an empty resultstring before generation
         */
        $this->resultString = '';

        /*
         * Loop the available types and generate the ORM property.
         */
        foreach (self::$propertyTypes as $type) {
            $function = 'generateORM' . $type . 'Properties';
            $this->{$function}($className);
        }
    }

    /**
     * Generate the Owner-properties for extensions.
     *
     * @param string $className
     */
    protected function generateORMOwnerProperties($className)
    {
        $owners = array();
        foreach ($this->objectList as $class) {
            $config = Config::inst()->get($class, 'extensions', Config::UNINHERITED);
            if ($config !== null && in_array($className, Config::inst()->get($class, 'extensions', Config::UNINHERITED), null)) {
                $owners[] = $class;
            }
        }
        if (count($owners)) {
            $this->resultString .= ' * @property ';
            foreach ($owners as $key => $owner) {
                if ($key > 0) {
                    $this->resultString .= '|';
                }
                $this->resultString .= "$owner";
            }
            $this->resultString .= "|$className owner\n";
        }
    }


    /**
     * Generate the $db property values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMDBProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'db', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $prop = 'string';

                $fieldObj = Object::create_from_string($dataObjectName, $fieldName);

                if (is_a($fieldObj, 'Int')) {
                    $prop = 'int';
                } elseif (is_a($fieldObj, 'Boolean')) {
                    $prop = 'boolean';
                } elseif (is_a($fieldObj, 'Float') || is_a($fieldObj, 'Decimal')) {
                    $prop = 'float';
                }
                $this->resultString .= " * @property $prop $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the $belongs_to property values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMBelongsToProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'belongs_to', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= ' * @property ' . $dataObjectName . " $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the $has_one property and method values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMHasOneProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'has_one', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= " * @property int {$fieldName}ID\n";
            }
            $this->resultString .= " * \n";
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= " * @method $dataObjectName $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the $has_many method values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMHasManyProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'has_many', Config::UNINHERITED)) {
            $this->resultString .= " * \n";
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= ' * @method DataList|' . $dataObjectName . "[] $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the $many_many method values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMManyManyProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'many_many', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= ' * @method ManyManyList|' . $dataObjectName . "[] $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the $belongs_many_many method values.
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMBelongsManyManyProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'belongs_many_many', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->resultString .= ' * @method ManyManyList|' . $dataObjectName . "[] $fieldName\n";
            }
        }

        return true;
    }

    /**
     * Generate the mixins for DataExtensions
     *
     * @param DataObject|DataExtension $className
     *
     * @return string
     */
    protected function generateORMExtensionsProperties($className)
    {
        if ($fields = Config::inst()->get($className, 'extensions', Config::UNINHERITED)) {
            $this->resultString .= " * \n";
            foreach ($fields as $fieldName) {
                $this->resultString .= " * @mixin $fieldName\n";
            }
        }

        return true;
    }
}
