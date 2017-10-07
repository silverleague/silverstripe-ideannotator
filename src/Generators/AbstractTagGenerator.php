<?php

namespace Axyr\IDEAnnotator;

use phpDocumentor\Reflection\DocBlock\Tag;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

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
     * The existing tags of the class we are working with
     * @var Tag[]
     */
    protected $existingTags = array();

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @see $this->getSupportedTagTypes();
     * @var Tag[]
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
     * @param $existingTags
     * @throws \ReflectionException
     */
    public function __construct($className, $existingTags)
    {
        $this->className = $className;
        $this->existingTags = (array)$existingTags;
        $this->reflector = new \ReflectionClass($className);
        $extendableClasses = Config::inst()->getAll();
        $this->extensionClasses = [];
        // We need to check all config to see if the class is extensible
        // @todo find a cleaner method
        foreach ($extendableClasses as $key => $configClass) {
            if (isset($configClass['extensions']) && count($configClass['extensions']) > 0) {
                $this->extensionClasses[] = $key;
            }
        }
        $this->tags = $this->getSupportedTagTypes();

        $this->generateTags();
    }

    /**
     * List of supported tags.
     *
     * Each tag type can hold many tags, so we keep them grouped.
     * Also used to reset the tag list after each run
     *
     * @return array
     */
    public function getSupportedTagTypes()
    {
        return array(
            'properties' => array(),
            'methods'    => array(),
            'mixins'     => array(),
            'other'      => array()
        );
    }

    /**
     * @return void
     */
    abstract protected function generateTags();

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return (array)call_user_func_array('array_merge', $this->tags);
    }

    /**
     * Returns the generated Tag objects as a string
     * with asterix and newline \n
     * @return string
     */
    public function getTagsAsString()
    {
        $tagString = '';

        foreach ($this->tags as $tag) {
            $tagString .= ' * ' . $tag . "\n";
        }

        return $tagString;
    }

    /**
     * Generate the mixins for DataExtensions.
     */
    protected function generateExtensionsTags()
    {
        if ($fields = (array)$this->getClassConfig('extensions')) {
            foreach ($fields as $fieldName) {
                $this->pushMixinTag("\\$fieldName");
            }
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getClassConfig($key)
    {
        return Config::inst()->get($this->className, $key, CONFIG::UNINHERITED);
    }

    /**
     * @param $tagString
     */
    protected function pushMixinTag($tagString)
    {
        $this->tags['mixins'][$tagString] = $this->pushTagWithExistingComment('mixin', $tagString);
    }

    /**
     * @param $type
     * @param $tagString
     * @return Tag
     */
    protected function pushTagWithExistingComment($type, $tagString)
    {
        $tagString .= $this->getExistingTagCommentByTagString($type, $tagString);

        return new Tag($type, $tagString);
    }

    /**
     * @param $type
     * @param string $tagString
     * @return mixed|string
     */
    public function getExistingTagCommentByTagString($type, $tagString)
    {
        foreach ($this->getExistingTags() as $tag) {
            $content = $tag->getContent();
            if (strpos($content, $tagString) !== false) {
                return str_replace($tagString, '', $content);
            }
        }

        return '';
    }

    /**
     * @return Tag[]
     */
    public function getExistingTags()
    {
        return $this->existingTags;
    }

    /**
     * Generate the Owner-properties for extensions.
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function generateOwnerTags()
    {
        $className = $this->className;
        if (Injector::inst()->get($this->className) instanceof Extension) {
            $owners = (array)array_filter($this->extensionClasses, function ($class) use ($className) {
                $config = Config::inst()->get($class, 'extensions');

                return ($config !== null && in_array($className, $config, null));
            });
            if (!empty($owners)) {
                $this->pushPropertyTag('\\' . implode("|\\", array_values($owners)) . ' $owner');
            }
        }
    }

    /**
     * @param $tagString
     */
    protected function pushPropertyTag($tagString)
    {
        $this->tags['properties'][$tagString] = $this->pushTagWithExistingComment('property', $tagString);
    }

    /**
     * @param string $methodName
     * @param string $tagString
     */
    protected function pushMethodTag($methodName, $tagString)
    {
        if (!$this->reflector->hasMethod($methodName)) {
            $this->tags['methods'][$tagString] = $this->pushTagWithExistingComment('method', $tagString);
        }
    }
}
