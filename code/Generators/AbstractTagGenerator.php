<?php

use phpDocumentor\Reflection\DocBlock\Tag;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;


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
     * @var phpDocumentor\Reflection\DocBlock\Tag[]
     */
    protected $existingTags = array();

    /**
     * @var ReflectionClass
     */
    protected $reflector;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @see $this->getSupportedTagTypes();
     * @var phpDocumentor\Reflection\DocBlock\Tag[]
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
    public function __construct($className, $existingTags)
    {
        $this->className        = $className;
        $this->existingTags     = (array)$existingTags;
        $this->reflector        = new ReflectionClass($className);
        $this->extensionClasses = (array)ClassInfo::subclassesFor('SilverStripe\\Core\\Object');
        $this->tags             = $this->getSupportedTagTypes();

        $this->generateTags();
    }

    /**
     * @return phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTags()
    {
        return (array)call_user_func_array('array_merge', $this->tags);
    }

    /**
     * @return phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getExistingTags()
    {
        return $this->existingTags;
    }

    /**
     * Returns the generated Tag objects as a string
     * with asterix and newline \n
     * @return string
     */
    public function getTagsAsString()
    {
        $tagString = '';

        foreach($this->tags as $tag) {
            $tagString .= ' * ' . $tag . "\n";
        }

        return $tagString;
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
            if($this->reflector->isSubclassOf('SilverStripe\\ORM\\DataExtension')) {
                $owners[] = $this->className;
            }
            $this->pushPropertyTag(implode("|", $owners) . " \$owner");
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

    /**
     * @param $tagString
     */
    protected function pushMixinTag($tagString)
    {
        $this->tags['mixins'][$tagString] = $this->pushTagWithExistingComment('mixin', $tagString);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getClassConfig($key)
    {
        return Config::inst()->get($this->className, $key, Config::UNINHERITED);
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

    public function getExistingTagCommentByTagString($type, $tagString)
    {
        foreach($this->getExistingTags() as $tag) {
            $content = $tag->getContent();
            if (strpos($content, $tagString) !== false) {
                return str_replace($tagString, '', $content);
            }
        }
        return '';
    }
}
