<?php

namespace SilverLeague\IDEAnnotator;

use InvalidArgumentException;
use LogicException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DB;

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
class DataObjectAnnotator
{
    use Injectable;
    use Configurable;
    use Extensible;

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
    private static $enabled_modules = ['mysite'];

    /**
     * @var AnnotatePermissionChecker
     */
    private $permissionChecker;

    /**
     * @var array
     */
    private $annotatableClasses = [];

    /**
     * DataObjectAnnotator constructor.
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        // Don't instantiate anything if annotations are not enabled.
        if (static::config()->get('enabled') === true && Director::isDev()) {
            $this->permissionChecker = Injector::inst()->get(AnnotatePermissionChecker::class);
            foreach ($this->permissionChecker->getSupportedParentClasses() as $supportedParentClass) {
                $this->setEnabledClasses($supportedParentClass);
            }
        }
    }

    /**
     * Get all annotatable classes from enabled modules
     */
    protected function setEnabledClasses($supportedParentClass)
    {
        foreach ((array)ClassInfo::subclassesFor($supportedParentClass) as $class) {
            $classInfo = new AnnotateClassInfo($class);
            if ($this->permissionChecker->moduleIsAllowed($classInfo->getModuleName())) {
                $this->annotatableClasses[$class] = $classInfo->getClassFilePath();
            }
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
            DB::alteration_message($className . ' is not writable by ' . get_current_user(), 'error');
        } else {
            $original = file_get_contents($filePath);
            $generated = $this->getGeneratedFileContent($original, $className);

            // we have a change, so write the new file
            if ($generated && $generated !== $original && $className) {
                // Trim unneeded whitespaces at the end of lines
                $generated = preg_replace('/[ \t]+$/m', '', $generated);
                file_put_contents($filePath, $generated);
                DB::alteration_message($className . ' Annotated', 'created');
            } elseif ($generated === $original && $className) {
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

        if ($existing) {
            $fileContent = str_replace($existing, $generated, $fileContent);
        } else {
            $needle = "class {$className}";
            $replace = "{$generated}\nclass {$className}";
            $pos = strpos($fileContent, $needle);
            if ($pos !== false) {
                $fileContent = substr_replace($fileContent, $replace, $pos, strlen($needle));
            } else {
                if (strrpos($className, "\\") !== false && class_exists($className)) {
                    $exploded = explode("\\", $className);
                    $classNameNew = end($exploded);
                    $needle = "class {$classNameNew}";
                    $replace = "{$generated}\nclass {$classNameNew}";
                    $pos = strpos($fileContent, $needle);
                    $fileContent = substr_replace($fileContent, $replace, $pos, strlen($needle));
                } else {
                    DB::alteration_message(
                        "Could not find string 'class $className'. Please check casing and whitespace.",
                        'error'
                    );
                }
            }
        }

        return $fileContent;
    }
}
