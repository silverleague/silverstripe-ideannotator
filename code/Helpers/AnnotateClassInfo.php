<?php

/**
 * Class AnnotateClassInfo
 * We will need this for phpDocumentor as well.
 *
 * @todo namespace this...
 *
 * @package IDEAnnotator/Helpers
 */
class AnnotateClassInfo
{
    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var ReflectionClass
     */
    protected $reflector;

    /**
     * AnnotateClassInfo constructor.
     *
     * @param $className
     */
    public function __construct($className)
    {
        $this->className = $className;

        $this->reflector = new ReflectionClass($className);
    }

    /**
     * Where module name is a folder in the webroot.
     *
     * @return string
     */
    public function getModuleName()
    {
        $relativePath     = str_replace(BASE_PATH . '/', '', $this->reflector->getFileName());
        list($moduleName) = explode(DIRECTORY_SEPARATOR, $relativePath);

        return (string)$moduleName;
    }

    /**
     * @return string
     */
    public function getClassFilePath()
    {
        return $this->reflector->getFileName();
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        return $this->reflector->getDocComment();
    }
}
