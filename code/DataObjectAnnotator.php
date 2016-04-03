<?php
use phpDocumentor\Reflection\DocBlock\Tag;

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
 *
 * @package IDEAnnotator/Core
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

    /**
     * @var AnnotatePermissionChecker
     */
    private $permissionChecker;

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
     * @var array
     */
    protected $dataExtensions;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @var array
     */
    protected $tags = array(
        'properties'=> array(),
        'methods'   => array(),
        'mixins'    => array()
    );

    public function __construct()
    {
        parent::__construct();
        $this->classes = ClassInfo::subclassesFor('DataObject');
        $this->extensionClasses = ClassInfo::subclassesFor('Object');
        $this->dataExtensions = ClassInfo::subclassesFor('DataExtension');
        $this->permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
    }

    /**
     * @param string $moduleName
     *
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     * @return false || void
     */
    public function annotateModule($moduleName)
    {
        if (!$this->permissionChecker->moduleIsAllowed($moduleName)) {
            return false;
        }

        foreach ($this->classes as $className) {
            $this->annotateDataObject($className);
        }

        foreach ($this->dataExtensions as $className) {
            $this->annotateDataObject($className);
        }

        return null;
    }

    /**
     * @param string     $className
     *
     * Generate docblock for a single subclass of DataObject or DataExtenions
     *
     * @return bool
     */
    public function annotateDataObject($className)
    {
        if (!$this->permissionChecker->classNameIsAllowed($className)) {
            return false;
        }

        $classInfo = new AnnotateClassInfo($className);
        $filePath  = $classInfo->getWritableClassFilePath();

        if (!$filePath) {
            return false;
        }

        $original = file_get_contents($filePath);
        $annotated = $this->getFileContentWithAnnotations($original, $className);

        // we have a change, so write the new file
        if ($annotated && $annotated !== $original) {
            file_put_contents($filePath, $annotated);
            DB::alteration_message($className . ' Annotated', 'created');
        }

        return true;
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

        if (!$this->tags) {
            return null;
        }

        $tagString = '';
        foreach($this->tags as $tagType) {
            foreach($tagType as $tag) {
                $tagString .= ' * ' . $tag . "\n";
            }
        }

        $startTag = static::STARTTAG;
        $endTag = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replacement = $startTag . "\n * \n" . $tagString . " * \n * " . $endTag;

            return preg_replace("/$startTag([\s\S]*?)$endTag/", $replacement, $fileContent);
        } else {
            $classDeclaration = 'class ' . $className . ' extends'; // add extends to exclude Controller writes
            $properties = "\n/**\n * " . $startTag . "\n * \n"
                . $tagString
                . " * \n * " . $endTag . "\n"
                . " */\n$classDeclaration";

            return str_replace($classDeclaration, $properties, $fileContent);
        }
    }

    /**
     * removes the unnecessary STARTTAG and ENDTAG
     *
     * @param $fileContent
     *
     * @return mixed
     */
    protected function removeStartAndEndTag($fileContent)
    {
        $startTag = static::STARTTAG;
        $endTag = static::ENDTAG;
        $replacements = array(
            "/ \* $startTag\n/",
            "/ \* $endTag\n/"
        );
        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $fileContent = preg_replace($replacements, '', $fileContent);
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
        $this->resetTags();

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
        foreach ($this->extensionClasses as $class) {
            $config = Config::inst()->get($class, 'extensions', Config::UNINHERITED);
            if ($config !== null && in_array($className, $config, null)) {
                $owners[] = $class;
            }
        }
        if (count($owners)) {
            $owners[] = $className;
            $tag = implode("|", $owners) . " \$owner";
            $this->tags['properties'][$tag] = new Tag('property', $tag);
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

                if ($fieldObj instanceof Int || $fieldObj instanceof DBInt) {
                    $prop = 'int';
                } elseif ($fieldObj instanceof Boolean) {
                    $prop = 'boolean';
                } elseif ($fieldObj instanceof Float ||
                    $fieldObj instanceof DBFloat ||
                    $fieldObj instanceof Decimal
                ) {
                    $prop = 'float';
                }
                $tag = "$prop \$$fieldName";
                $this->tags['properties'][$tag] = new Tag('property', $tag);
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
            //$this->resultString .= " * \n";
            foreach ($fields as $fieldName => $dataObjectName) {
                $tag = $dataObjectName . " \$$fieldName";
                $this->tags['methods'][$tag] = new Tag('method', $tag);
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
                $tag = "int \${$fieldName}ID";
                $this->tags['properties'][$tag] = new Tag('property', $tag);
            }
            foreach ($fields as $fieldName => $dataObjectName) {
                $tag = "{$dataObjectName} {$fieldName}()";
                $this->tags['methods'][$tag] = new Tag('method', $tag);
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
            //$this->resultString .= " * \n";
            foreach ($fields as $fieldName => $dataObjectName) {
                $tag = "DataList|{$dataObjectName}[] {$fieldName}()";
                $this->tags['methods'][$tag] = new Tag('method', $tag);
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
                $tag = "ManyManyList|{$dataObjectName}[] {$fieldName}()";
                $this->tags['methods'][$tag] = new Tag('method', $tag);
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
                $tag = "ManyManyList|{$dataObjectName}[] {$fieldName}()";
                $this->tags['methods'][$tag] = new Tag('method', $tag);
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
            //$this->resultString .= " * \n";
            foreach ($fields as $fieldName) {
                $this->tags['mixins'][$fieldName] = new Tag('mixin',$fieldName);
            }
        }

        return true;
    }

    /**
     * Reset the tag list after each run
     */
    protected function resetTags()
    {
        $this->tags = array(
            'properties'=> array(),
            'methods'   => array(),
            'mixins'    => array()
        );
    }
}
