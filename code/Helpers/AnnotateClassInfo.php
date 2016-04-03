<?php

/**
 * Class AnnotateClassInfo
 * We will need this for phpDocumentor as well.
 *
 * @todo namespace this...
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

    public function __construct($className)
    {
        $this->className = $className;

        $this->reflector = new ReflectionClass($className);
    }

    /**
     * If the file writable, return the absolute path of the file that holds the ClassName
     * @todo should we check here for writable?
     *
     * @return string
     */
    public function getWritableClassFilePath()
    {
        $filePath = $this->reflector->getFileName();

        if (is_writable($filePath)) {
            return $filePath;
        }

        return false;
    }

}
