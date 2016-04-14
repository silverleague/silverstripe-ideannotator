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
     * Generate the mixins for DataExtensions.
     */
    protected function generateExtensionsTags()
    {
        if ($fields = (array)$this->getClassConfig('extensions')) {
            foreach ($fields as $fieldName) {
                $this->pushMixinTag($fieldName);
            }
        }
    }

    /**
     * Generate the Owner-properties for extensions.
     */
    protected function generateOwnerTags()
    {
        $className = $this->className;
        $owners = (array)array_filter($this->extensionClasses, function($class) use ($className) {
            $config = Config::inst()->get($class, 'extensions', Config::UNINHERITED);
            return ($config !== null && in_array($className, $config, null));
        });

        if (!empty($owners)) {
            $owners[] = $this->className;
            $this->pushPropertyTag(implode("|", $owners) . " \$owner");
        }
    }

    /**
     * @param $tagString
     */
    protected function pushPropertyTag($tagString)
    {
        $this->tags['properties'][$tagString] = new Tag('property', $tagString);
    }

    /**
     * @param string $methodName
     * @param string $tagString
     */
    protected function pushMethodTag($methodName, $tagString)
    {
        if (!$this->reflector->hasMethod($methodName)) {
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
