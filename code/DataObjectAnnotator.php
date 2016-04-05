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
     * All classes that subclass DataObject
     * @var array
     */
    protected $classes;

    /**
     * List of all objects, so we can find the extensions.
     * @var array
     */
    protected $dataExtensions;

    /**
     * Temporary flag so we can switch implementations.
     * Keep it public for easy checking outside this class.
     * @var bool
     */
    public static $phpDocumentorEnabled = false;

    /**
     * DataObjectAnnotator constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Don't instantiate anything if annotations are not enabled.
        if(static::config()->get('enabled') === true) {
            $this->classes = ClassInfo::subclassesFor('DataObject');
            $this->dataExtensions = ClassInfo::subclassesFor('DataExtension');
        }
    }

    /**
     * @return bool
     *
     * Generate docblock for all subclasses of DataObjects and DataExtenions
     * within a module.
     *
     */
    public function annotateModule()
    {
        foreach ($this->classes as $className) {
            $this->annotateDataObject($className);
        }

        foreach ($this->dataExtensions as $className) {
            $this->annotateDataObject($className);
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
    public function annotateDataObject($className)
    {
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
     * @return string|boolean
     */
    protected function getFileContentWithAnnotations($fileContent, $className)
    {
        $generator = new DocBlockTagGenerator($className);

        $tagString = $generator->getTagsAsString();

        if (!$tagString) {
            return false;
        }

        $startTag = static::STARTTAG;
        $endTag = static::ENDTAG;

        if (strpos($fileContent, $startTag) && strpos($fileContent, $endTag)) {
            $replacement = $startTag . "\n * \n" . $tagString . " * \n * " . $endTag;
            return preg_replace("/$startTag([\s\S]*?)$endTag/", $replacement, $fileContent);
        }

        $classDeclaration = 'class ' . $className . ' extends'; // add extends to exclude Controller writes
        $properties = "\n/**\n * " . $startTag . "\n * \n"
            . $tagString
            . " * \n * " . $endTag . "\n"
            . " */\n$classDeclaration";

        return str_replace($classDeclaration, $properties, $fileContent);
    }

}
