<?php

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Class DocBlockTagGenerator
 *
 * @package IDEAnnotator/Generators
 */
class DocBlockTagGenerator
{
    /**
     * @var array
     * Available properties to generate docblocks for.
     */
    protected static $propertyTypes = array(
        'Owner',
        'DB',
        'HasOne',
        'BelongsTo',
        'HasMany',
        'ManyMany',
        'BelongsManyMany',
        'Extensions',
    );

    /**
     * All classes that subclass Object
     * @var array
     */
    protected $extensionClasses;

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

        $this->generateORMProperties();
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
     * Generates all ORM Properties
     */
    protected function generateORMProperties()
    {
        foreach (self::$propertyTypes as $type) {
            $function = 'generateORM' . $type . 'Properties';
            $this->{$function}();
        }
    }

    /**
     * Generate the Owner-properties for extensions.
     */
    protected function generateORMOwnerProperties()
    {
        $owners = array();
        foreach ($this->extensionClasses as $class) {
            $config = Config::inst()->get($class, 'extensions', Config::UNINHERITED);
            if ($config !== null && in_array($this->className, $config, null)) {
                $owners[] = $class;
            }
        }
        if (count($owners)) {
            $owners[] = $this->className;
            $this->pushPropertyTag(implode("|", $owners) . " \$owner");
        }
    }

    /**
     * Generate the $db property values.
     */
    protected function generateORMDBProperties()
    {
        if ($fields = (array)Config::inst()->get($this->className, 'db', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $prop = 'string';

                $fieldObj = Object::create_from_string($dataObjectName, $fieldName);

                if ($fieldObj instanceof Int || $fieldObj instanceof DBInt) {
                    $prop = 'int';
                } elseif ($fieldObj instanceof Boolean) {
                    $prop = 'boolean';
                } elseif ($fieldObj instanceof Float || $fieldObj instanceof DBFloat || $fieldObj instanceof Decimal) {
                    $prop = 'float';
                }

                $this->pushPropertyTag("$prop \$$fieldName");
            }
        }
    }

    /**
     * Generate the $belongs_to property values.
     */
    protected function generateORMBelongsToProperties()
    {
        if ($fields = (array)Config::inst()->get($this->className, 'belongs_to', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->pushMethodTag($fieldName, $dataObjectName . " \$$fieldName");
            }
        }
    }

    /**
     * Generate the $has_one property and method values.
     */
    protected function generateORMHasOneProperties()
    {
        if ($fields = (array)Config::inst()->get($this->className, 'has_one', Config::UNINHERITED)) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->pushPropertyTag("int \${$fieldName}ID");
                $this->pushMethodTag($fieldName, "{$dataObjectName} {$fieldName}()");
            }
        }
    }

    /**
     * Generate the $has_many method values.
     */
    protected function generateORMHasManyProperties()
    {
        $this->generateTagsForDataLists(
            Config::inst()->get($this->className, 'has_many', Config::UNINHERITED),
            'DataList'
        );
    }

    /**
     * Generate the $many_many method values.
     */
    protected function generateORMManyManyProperties()
    {
        $this->generateTagsForDataLists(
            Config::inst()->get($this->className, 'many_many', Config::UNINHERITED),
            'ManyManyList'
        );
    }

    /**
     * Generate the $belongs_many_many method values.
     */
    protected function generateORMBelongsManyManyProperties()
    {
        $this->generateTagsForDataLists(
            Config::inst()->get($this->className, 'belongs_many_many', Config::UNINHERITED),
            'ManyManyList'
        );
    }

    /**
     * Generate the mixins for DataExtensions.
     */
    protected function generateORMExtensionsProperties()
    {
        if ($fields = (array)Config::inst()->get($this->className, 'extensions', Config::UNINHERITED)) {
            foreach ($fields as $fieldName) {
                $this->tags['mixins'][$fieldName] = new Tag('mixin',$fieldName);
            }
        }
    }

    /**
     * @param array $fields
     * @param string $listType
     */
    protected function generateTagsForDataLists($fields, $listType = 'DataList')
    {
        if(!empty($fields)) {
            foreach ((array)$fields as $fieldName => $dataObjectName) {
                $this->pushMethodTag($fieldName, "{$listType}|{$dataObjectName}[] {$fieldName}()");
            }
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
     * @param string $fieldName
     * @param string $tagString
     */
    protected function pushMethodTag($fieldName, $tagString)
    {
        if (!$this->reflector->hasMethod($fieldName)) {
            $this->tags['methods'][$tagString] = new Tag('method', $tagString);
        }
    }
}
