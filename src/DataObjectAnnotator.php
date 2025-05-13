<?php

namespace SilverLeague\IDEAnnotator;

use InvalidArgumentException;
use LogicException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use SilverLeague\IDEAnnotator\Generators\DocBlockGenerator;
use SilverLeague\IDEAnnotator\Helpers\AnnotateClassInfo;
use SilverLeague\IDEAnnotator\Helpers\AnnotatePermissionChecker;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBInt;
use stdClass;

/**
 * Class DataObjectAnnotator
 * Generates phpdoc annotations for database fields and orm relations
 * so IDE's with autocompletion and property inspection will recognize properties
 * and relation methods.
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
class DataObjectAnnotator
{
    use Injectable;
    use Configurable;
    use Extensible;

    /**
     * All classes that subclass Object
     *
     * @var array
     */
    protected static $extension_classes = [];

    /**
     * @config
     * Enable generation from @see Annotatable and @see DataObjectAnnotatorTask
     *
     * @var bool
     */
    private static $enabled = false;

    /**
     * @config
     * Enable modules that are allowed to have generated docblocks for
     * DataObjects and DataExtensions
     *
     * @var array
     */
    private static $enabled_modules = ['mysite', 'app'];

    /**
     * @var AnnotatePermissionChecker
     */
    private $permissionChecker;

    /**
     * @var array
     */
    private $annotatableClasses = [];

    /**
     * Default tagname will be @string .
     * All exceptions for @see DBField types are listed here
     *
     * @see generateDBTags();
     * @config Can be overridden via config
     * @var array
     */
    protected static $dbfield_tagnames = [
        DBInt::class     => 'int',
        DBBoolean::class => 'bool',
        DBFloat::class   => 'float',
        DBDecimal::class => 'float',
    ];

    /**
     * DataObjectAnnotator constructor.
     *
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct()
    {
        // Don't instantiate anything if annotations are not enabled.
        if (static::config()->get('enabled') === true && Director::isDev()) {
            $this->extend('beforeDataObjectAnnotator');

            $this->setupExtensionClasses();

            $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);

            foreach ($this->permissionChecker->getSupportedParentClasses() as $supportedParentClass) {
                $this->setEnabledClasses($supportedParentClass);
            }

            $this->extend('afterDataObjectAnnotator');
        }
    }

    /**
     * Named `setup` to not clash with the actual setter
     *
     * Loop all extendable classes and see if they actually have extensions
     * If they do, add it to the array
     * Clean up the array of duplicates
     * Then save the setup of the classes in a static array, this is to save memory
     *
     * @throws ReflectionException
     */
    protected function setupExtensionClasses()
    {
        $extension_classes = [];

        $extendableClasses = Config::inst()->getAll();

        // We need to check all config to see if the class is extensible
        foreach ($extendableClasses as $className => $classConfig) {
            if (!class_exists($className)) {
                continue;
            }

            // If the class doesn't already exist in the extension classes
            if (in_array($className, self::$extension_classes)) {
                continue;
            }

            // And the class has extensions,
            $extensions = DataObject::get_extensions($className);
            if (!count($extensions)) {
                continue;
            }

            // Add it.
            $extension_classes[] = ClassInfo::class_name($className);
        }

        $extension_classes = array_unique($extension_classes);

        static::$extension_classes = $extension_classes;
    }

    /**
     * Get all annotatable classes from enabled modules
     * @param string|StdClass $supportedParentClass
     * @throws ReflectionException
     */
    protected function setEnabledClasses($supportedParentClass)
    {
        foreach ((array)ClassInfo::subclassesFor($supportedParentClass) as $class) {
            if (!class_exists($class)) {
                continue;
            }
            $classInfo = new AnnotateClassInfo($class);
            if ($this->permissionChecker->moduleIsAllowed($classInfo->getModuleName())) {
                $this->annotatableClasses[$class] = $classInfo->getClassFilePath();
            }
        }
    }

    /**
     * @return array
     */
    public static function getExtensionClasses()
    {
        return self::$extension_classes;
    }

    /**
     * @param array $extension_classes
     */
    public static function setExtensionClasses($extension_classes)
    {
        self::$extension_classes = $extension_classes;
    }

    /**
     * Add another extension class
     * False checking, because what we get might be uppercase and then lowercase
     * Allowing for duplicates here, to clean up later
     *
     * @param string $extension_class
     */
    public static function pushExtensionClass($extension_class)
    {
        if (!in_array($extension_class, self::$extension_classes)) {
            self::$extension_classes[] = $extension_class;
        }
    }

    /**
     * @return boolean
     */
    public static function isEnabled()
    {
        return (bool)static::config()->get('enabled');
    }

    /**
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     * @param string $moduleName
     * @return bool
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function annotateModule($moduleName)
    {
        if (!(bool)$moduleName || !$this->permissionChecker->moduleIsAllowed($moduleName)) {
            return false;
        }

        $classes = (array)$this->getClassesForModule($moduleName);

        foreach ($classes as $className => $filePath) {
            $this->annotateObject($className);
        }

        return true;
    }

    /**
     * @param $moduleName
     * @return array
     * @throws ReflectionException
     */
    public function getClassesForModule($moduleName)
    {
        $classes = [];

        foreach ($this->annotatableClasses as $class => $filePath) {
            $classInfo = new AnnotateClassInfo($class);
            if ($moduleName === $classInfo->getModuleName()) {
                $classes[$class] = $filePath;
            }
        }

        return $classes;
    }

    /**
     * Generate docblock for a single subclass of DataObject or DataExtenions
     *
     * @param string $className
     * @return bool
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws NotFoundExceptionInterface
     */
    public function annotateObject($className)
    {
        if (!$this->permissionChecker->classNameIsAllowed($className)) {
            return false;
        }

        $this->writeFileContent($className);

        return true;
    }

    /**
     * @param string $className
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    protected function writeFileContent($className)
    {
        $classInfo = new AnnotateClassInfo($className);
        $filePath = $classInfo->getClassFilePath();

        if (!is_writable($filePath)) {
            // Unsure how to test this properly
            DB::alteration_message($className . ' is not writable by ' . get_current_user(), 'error');
        } else {
            $original = file_get_contents($filePath);
            $generated = $this->getGeneratedFileContent($original, $className);

            // we have a change, so write the new file
            if ($generated && $generated !== $original && $className) {
                file_put_contents($filePath, $generated);
                DB::alteration_message($className . ' Annotated', 'created');
            } elseif ($generated === $original && $className) {
                // Unsure how to test this properly
                DB::alteration_message($className, 'repaired');
            }
        }
    }

    /**
     * Return the complete File content with the newly generated DocBlocks
     *
     * @param string $fileContent
     * @param string $className
     * @return mixed
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    protected function getGeneratedFileContent($fileContent, $className)
    {
        $generator = new DocBlockGenerator($className);

        $existing = $generator->getExistingDocBlock();
        $generated = $generator->getGeneratedDocBlock();

        // Trim unneeded whitespaces at the end of lines for PSR-2
        $generated = preg_replace('/\s+$/m', '', $generated);

        // $existing could be a boolean that in theory is `true`
        // It never is though (according to the generator's doc)
        if ((bool)$existing !== false) {
            $fileContent = str_replace($existing, $generated, $fileContent);
        } else {
            if (class_exists($className)) {
                $exploded = ClassInfo::shortName($className);
                $needle = "class {$exploded}";
                $replace = "{$generated}\nclass {$exploded}";
                $pos = strpos($fileContent, $needle);
                $fileContent = substr_replace($fileContent, $replace, $pos, strlen($needle));
            } else {
                DB::alteration_message(
                    "Could not find string 'class $className'. Please check casing and whitespace.",
                    'error'
                );
            }
        }

        return $fileContent;
    }
}
