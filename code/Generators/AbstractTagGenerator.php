<?php

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * AbstractTagGenerator
 *
 * @package IDEAnnotator/Generators
 */
abstract class AbstractTagGenerator
{
    /**
     * The current class we are working with
     * @var string
     */
    protected $className = '';

    /**
     * @var ReflectionClass
     */
    protected $reflector;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @see $this->getSupportedTagTypes();
     * @var array
     */
    protected $tags = array();

    /**
     * All classes that subclass Object
     * @var array
     */
    protected $extensionClasses;

    /**
     * DocBlockTagGenerator constructor.
     *
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className        = $className;
        $this->reflector        = new ReflectionClass($className);
        $this->extensionClasses = (array)ClassInfo::subclassesFor('Object');
        $this->tags             = $this->getSupportedTagTypes();

        $this->generateTags();
    }

    /**
     * @return phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTags()
    {
        return (array)$this->tags;
    }

    /**
     * Returns the generated Tag objects as a string
     * with asterix and newline \n
     * @return string
     */
    public function getTagsAsString()
    {
        $tagString = '';

        foreach($this->tags as $tagType) {
            foreach($tagType as $tag) {
                $tagString .= ' * ' . $tag . "\n";
            }
        }

        return $tagString;
    }

    /**
     * Reset the tag list after each run
     */
    public function getSupportedTagTypes()
    {
        return array(
            'properties'=> array(),
            'methods'   => array(),
            'mixins'    => array(),
            'other'     => array()
        );
    }

    /**
     * @return void
     */
    abstract protected function generateTags();

    /**
     * @param $tagString
     */
    protected function pushPropertyTag($tagString)
    {
        $this->tags['properties'][$tagString] = new Tag('property', $tagString);
    }

    /**
     * @param string $fieldName
     * @param string $tagString
     */
    protected function pushMethodTag($fieldName, $tagString)
    {
        if (!$this->reflector->hasMethod($fieldName)) {
            $this->tags['methods'][$tagString] = new Tag('method', $tagString);
        }
    }

    /**
     * @param $tagString
     */
    protected function pushMixinTag($tagString)
    {
        $this->tags['mixins'][$tagString] = new Tag('mixin', $tagString);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getClassConfig($key)
    {
        return Config::inst()->get($this->className, $key, Config::UNINHERITED);
    }
}
