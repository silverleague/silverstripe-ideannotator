<?php

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Serializer as DocBlockSerializer;

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
     * If the file writable, return the absolute path of the file that holds the ClassName
     * @todo should we check here for writable?
     *
     * @return string|false
     */
    public function getWritableClassFilePath()
    {
        $filePath = $this->reflector->getFileName();

        if (is_writable($filePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        $docblock = $this->reflector->getDocComment();

        return $docblock;
    }
}
