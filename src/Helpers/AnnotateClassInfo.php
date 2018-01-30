<?php

namespace SilverLeague\IDEAnnotator;

use ReflectionClass;
use ReflectionException;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleManifest;

/**
 * Class AnnotateClassInfo
 * We will need this for phpDocumentor as well.
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
     * @throws ReflectionException
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
        /** @var ModuleManifest $moduleManifest */
        $moduleManifest = Injector::inst()->createWithArgs(ModuleManifest::class, [Director::baseFolder()]);
        $module = $moduleManifest->getModuleByPath($this->reflector->getFileName());

        return $module->getName();
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
