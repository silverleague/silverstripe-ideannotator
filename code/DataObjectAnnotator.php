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
     * @var AnnotatePermissionChecker
     */
    private $permissionChecker;

    /**
     * @var array
     */
    private $annotatableClasses = array();

    /**
     * DataObjectAnnotator constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Don't instantiate anything if annotations are not enabled.
        if(static::config()->get('enabled') === true) {
            $this->permissionChecker = Injector::inst()->get('AnnotatePermissionChecker');
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
        foreach((array)ClassInfo::subclassesFor($supportedParentClass) as $class)
        {
            $classInfo = new AnnotateClassInfo($class);
            if($this->permissionChecker->moduleIsAllowed($classInfo->getModuleName())) {
                $this->annotatableClasses[$class] = $classInfo->getWritableClassFilePath();
            }
        }
    }

    /**
     * @param $moduleName
     * @return array
     */
    public function getClassesForModule($moduleName)
    {
        $classes = array();

        foreach ($this->annotatableClasses as $class => $filePath) {
            $classInfo = new AnnotateClassInfo($class);
            if($moduleName === $classInfo->getModuleName()) {
                $classes[$class] = $filePath;
            }
        }

        return $classes;
    }

    /**
     * @param string $moduleName
     *
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     * @return bool
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
     * @param string     $className
     *
     * Generate docblock for a single subclass of DataObject or DataExtenions
     *
     * @return bool
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
     */
    protected function writeFileContent($className)
    {
        $classInfo = new AnnotateClassInfo($className);
        $filePath  = $classInfo->getWritableClassFilePath();

        if ($filePath !== false) {
            $original  = file_get_contents($filePath);
            $generated = $this->getGeneratedFileContent($original, $className);

            // we have a change, so write the new file
            if ($generated && $generated !== $original) {
                file_put_contents($filePath, $generated);
                DB::alteration_message($className . ' Annotated', 'created');
            }else{
                DB::alteration_message($className);
            }
        }
    }

    /**
     * @param $fileContent
     * @param $className
     *
     * Return the complete File content with the newly generated DocBlocks
     *
     * @return mixed
     */
    protected function getGeneratedFileContent($fileContent, $className)
    {
        $generator = new DocBlockGenerator($className);

        $existing  = $generator->getExistingDocBlock();
        $generated = $generator->getGeneratedDocBlock();

        if($existing) {
            $fileContent = str_replace($existing, $generated, $fileContent);
        }else{
            $needle = "class {$className}";
            $replace = "{$generated}\nclass {$className}";
            $pos = strpos($fileContent, $needle);
            if ($pos !== false) {
                $fileContent = substr_replace($fileContent, $replace, $pos, strlen($needle));
            }
        }

        return $fileContent;
    }

    /**
     * @return boolean
     */
    public static function isEnabled()
    {
        return (bool)static::config()->get('enabled');
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
        return (array)static::config()->get('enabled_modules');
    }

    /**
     * @param array $enabled_modules
     */
    public static function setEnabledModules($enabled_modules)
    {
        static::config()->enabled_modules = $enabled_modules;
    }

}
