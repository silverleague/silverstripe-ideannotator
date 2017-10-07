<?php

namespace Axyr\IDEAnnotator;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBDecimal;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ManyManyList;

/**
 * OrmTagGenerator
 * This class generates DocBlock Tags for the ORM properties of a Dataobject of DataExtension
 * and adds $owner Tags for added DataExtensions
 *
 * @package IDEAnnotator/Generators
 */
class OrmTagGenerator extends AbstractTagGenerator
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
     * Default tagname is will be @string .
     * All exceptions for @see DBField types are listed here
     *
     * @see generateDBTags();
     * @var array
     */
    protected static $dbfield_tagnames = array(
        DBInt::class     => 'int',
        DBBoolean::class => 'boolean',
        DBFloat::class   => 'float',
        DBDecimal::class => 'float',
    );

    /**
     * Generates all ORM Tags
     */
    protected function generateTags()
    {
        foreach (self::$propertyTypes as $type) {
            $function = 'generate' . $type . 'Tags';
            $this->{$function}();
        }
    }

    /**
     * Generate the $db property values.
     */
    protected function generateDBTags()
    {
        if ($fields = (array)$this->getClassConfig('db')) {
            foreach ($fields as $fieldName => $dbFieldType) {
                $this->pushPropertyTag($this->getTagNameForDBField($dbFieldType) . " \$$fieldName");
            }
        }
    }

    /**
     * @param string $dbFieldType
     * @return string
     */
    public function getTagNameForDBField($dbFieldType)
    {
        // some fields in 3rd-party modules require a name...
        $fieldObj = Injector::inst()->create($dbFieldType, 'DummyName');

        foreach (self::$dbfield_tagnames as $dbClass => $tagName) {
            if (class_exists($dbClass)) {
                $obj = Injector::inst()->create($dbClass);
                if ($fieldObj instanceof $obj) {
                    return $tagName;
                }
            }
        }

        return 'string';
    }

    /**
     * Generate the $belongs_to property values.
     */
    protected function generateBelongsToTags()
    {
        if ($fields = (array)$this->getClassConfig('belongs_to')) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $dataObjectName = $this->resolveDotNotation($dataObjectName);
                $this->pushMethodTag($fieldName, "\\{$dataObjectName} {$fieldName}()");
            }
        }
    }

    /**
     * @param $dataObjectName
     * @return mixed
     */
    protected function resolveDotNotation($dataObjectName)
    {
        list($dataObjectName) = explode('.', $dataObjectName, 2);

        return $dataObjectName;
    }

    /**
     * Generate the $has_one property and method values.
     */
    protected function generateHasOneTags()
    {
        if ($fields = (array)$this->getClassConfig('has_one')) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->pushPropertyTag("int \${$fieldName}ID");
                $this->pushMethodTag($fieldName, "\\{$dataObjectName} {$fieldName}()");
            }
        }
    }

    /**
     * Generate the $has_many method values.
     */
    protected function generateHasManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('has_many'), DataList::class);
    }

    /**
     * @param array $fields
     * @param string $listType
     */
    protected function generateTagsForDataLists($fields, $listType = DataList::class)
    {
        if (!empty($fields)) {
            foreach ((array)$fields as $fieldName => $dataObjectName) {
                $dataObjectName = $this->resolveDotNotation($dataObjectName);
                $this->pushMethodTag($fieldName, "\\{$listType}|\\{$dataObjectName}[] {$fieldName}()");
            }
        }
    }

    /**
     * Generate the $many_many method values.
     */
    protected function generateManyManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('many_many'), ManyManyList::class);
    }

    /**
     * Generate the $belongs_many_many method values.
     */
    protected function generateBelongsManyManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('belongs_many_many'), ManyManyList::class);
    }
}
