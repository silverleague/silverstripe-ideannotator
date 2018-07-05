<?php

namespace SilverLeague\IDEAnnotator\Generators;

use phpDocumentor\Reflection\DocBlock\Tag;
use Psr\Container\NotFoundExceptionInterface;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Core\ClassInfo;
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
    protected $existingTags = [];

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @see $this->getSupportedTagTypes();
     * @var Tag[]
     */
    protected $tags = [];

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
        return [
            'properties' => [],
            'methods'    => [],
            'mixins'     => [],
            'other'      => []
        ];
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
     * Generate the mixins for DataExtensions.
     */
    protected function generateExtensionsTags()
    {
        if ($fields = (array)$this->getClassConfig('extensions')) {
            foreach ($fields as $fieldName) {
                $mixinName = $this->getAnnotationClassName($fieldName);
                $this->pushMixinTag($mixinName);
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
     * Check if we need to use the short name for a class
     *
     * @param string $class
     * @return string
     */
    protected function getAnnotationClassName($class)
    {
        if (DataObjectAnnotator::config()->get('use_short_name')) {
            return ClassInfo::shortName($class);
        }

        return "\\$class";
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
        $tagString .= $this->getExistingTagCommentByTagString($tagString);

        return new Tag($type, $tagString);
    }

    /**
     * @param string $tagString
     * @return string
     */
    public function getExistingTagCommentByTagString($tagString)
    {
        foreach ($this->getExistingTags() as $tag) {
            $content = $tag->getContent();
            // A tag should be followed by a space before it's description
            // This is to prevent `TestThing` and `Test` to be seen as the same, when the shorter
            // is after the longer name
            if (strpos($content, $tagString . ' ') !== false) {
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
     *
     * @throws NotFoundExceptionInterface
     */
    protected function generateOwnerTags()
    {
        $className = $this->className;
        if (Injector::inst()->get($this->className) instanceof Extension) {
            $owners = array_filter(DataObjectAnnotator::getExtensionClasses(), function ($class) use ($className) {
                $config = Config::inst()->get($class, 'extensions');

                return ($config !== null && in_array($className, $config, null));
            });
            $owners[] = $this->className;
            $tagString = '\\' . implode("|\\", array_values($owners)) . ' $owner';
            if (DataObjectAnnotator::config()->get('use_short_name')) {
                foreach ($owners as $key => $owner) {
                    $owners[$key] = $this->getAnnotationClassName($owner);
                }
                $tagString = implode("|", array_values($owners)) . ' $owner';
            }
            $this->pushPropertyTag($tagString);
        }
    }

    /**
     * @param string $tagString
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
        // Exception for `data()` method is needed
        if (!$this->reflector->hasMethod($methodName) || $methodName === 'data()') {
            $this->tags['methods'][$tagString] = $this->pushTagWithExistingComment('method', $tagString);
        }
    }
}
