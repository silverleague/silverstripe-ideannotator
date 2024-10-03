<?php

namespace SilverLeague\IDEAnnotator\Generators;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\AbstractPHPStanFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyWriteFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ReturnFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\TypeResolver;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverLeague\IDEAnnotator\Reflection\ShortNameResolver;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;
use Generator;
use ReflectionClass;
use ReflectionException;

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
     * @var ReflectionClass
     */
    protected $reflector;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * List all the generated tags form the various generateSomeORMProperies methods
     * @see $this->getSupportedTagTypes();
     * @var Tag[]
     */
    protected $tags = [];

    protected static $pageClassesCache = [];

    /**
     * DocBlockTagGenerator constructor.
     *
     * @param string $className
     * @param        $existingTags
     * @throws ReflectionException
     */
    public function __construct($className, $existingTags)
    {
        $this->className = $className;
        $this->existingTags = (array)$existingTags;
        $this->reflector = new ReflectionClass($className);
        $this->tags = $this->getSupportedTagTypes();

        //Init the tag factory
        if (DataObjectAnnotator::config()->get('use_short_name')) {
            $this->docBlockFactory = $this->createShortNameFactory();
        } else {
            $this->docBlockFactory = DocBlockFactory::createInstance();
        }

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
        return (array)call_user_func_array('array_merge', array_values($this->tags));
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
        if (is_subclass_of($this->className, DataObject::class)) {
            $baseFields = Config::inst()->get(DataObject::class, 'extensions', Config::UNINHERITED);
            if ($baseFields) {
                foreach ($baseFields as $fieldName) {
                    $mixinName = $this->getAnnotationClassName($fieldName);
                    $this->pushMixinTag($mixinName);
                }
            }
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getClassConfig($key)
    {
        return Config::inst()->get($this->className, $key, Config::UNINHERITED);
    }

    /**
     * Check if we need to use the short name for a class
     *
     * @param string $class
     * @return string
     */
    protected function getAnnotationClassName($class)
    {
        [$class] = explode('.', $class); // Remove dot-notated extension parts
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
        $tagString = sprintf('@%s %s', $type, $tagString);
        $tagString .= $this->getExistingTagCommentByTagString($tagString);

        $tmpBlock = $this->docBlockFactory->create("/**\n* " . $tagString . "\n*/");
        return $tmpBlock->getTagsByName($type)[0];
    }

    /**
     * @param string $tagString
     * @return string
     */
    public function getExistingTagCommentByTagString($tagString)
    {
        foreach ($this->getExistingTags() as $tag) {
            $content = $tag->__toString();
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
     * @throws ReflectionException
     */
    protected function generateOwnerTags()
    {
        $className = $this->className;
        // If className is abstract, Injector will fail to instantiate it
        $reflection = new ReflectionClass($className);
        if ($reflection->isAbstract()) {
            return;
        }
        if ($reflection->isSubclassOf(Extension::class)) {
            $owners = iterator_to_array($this->getOwnerClasses($className));
            $owners[] = $this->className;
            $tagString = sprintf('\\%s $owner', implode("|\\", array_values($owners)));
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
     * Get all owner classes of the given extension class
     *
     * @param string $extensionClass Class name of the extension
     * @return string[]|Generator List of all direct owners of this extension
     */
    protected function getOwnerClasses($extensionClass)
    {
        foreach (DataObjectAnnotator::getExtensionClasses() as $objectClass) {
            $config = Config::inst()->get(
                $objectClass,
                'extensions',
                Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES
            ) ?: [];
            foreach ($config as $candidateClass) {
                if (Extension::get_classname_without_arguments($candidateClass) === $extensionClass) {
                    yield $objectClass;
                    break;
                }
            }
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

    /**
     * Factory method for easy instantiation.
     * @param array<string, class-string<Tag>|Factory> $additionalTags
     * @return DocBlockFactoryInterface
     */
    protected function createShortNameFactory(array $additionalTags = []): DocBlockFactory
    {
        $fqsenResolver = new ShortNameResolver();
        $tagFactory = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $typeResolver = new TypeResolver($fqsenResolver);

        $phpstanTagFactory = new AbstractPHPStanFactory(
            new ParamFactory($typeResolver, $descriptionFactory),
            new VarFactory($typeResolver, $descriptionFactory),
            new ReturnFactory($typeResolver, $descriptionFactory),
            new PropertyFactory($typeResolver, $descriptionFactory),
            new PropertyReadFactory($typeResolver, $descriptionFactory),
            new PropertyWriteFactory($typeResolver, $descriptionFactory),
            new MethodFactory($typeResolver, $descriptionFactory)
        );

        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService($typeResolver);
        $tagFactory->registerTagHandler('param', $phpstanTagFactory);
        $tagFactory->registerTagHandler('var', $phpstanTagFactory);
        $tagFactory->registerTagHandler('return', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property-read', $phpstanTagFactory);
        $tagFactory->registerTagHandler('property-write', $phpstanTagFactory);
        $tagFactory->registerTagHandler('method', $phpstanTagFactory);

        $docBlockFactory = new DocBlockFactory($descriptionFactory, $tagFactory);
        foreach ($additionalTags as $tagName => $tagHandler) {
            $docBlockFactory->registerTagHandler($tagName, $tagHandler);
        }

        return $docBlockFactory;
    }
}
