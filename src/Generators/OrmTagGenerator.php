<?php

namespace SilverLeague\IDEAnnotator\Generators;

use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
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
    protected static $propertyTypes = [
        'Owner',
        'DB',
        'HasOne',
        'BelongsTo',
        'HasMany',
        'ManyMany',
        'BelongsManyMany',
        'Extensions',
    ];

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

        $fieldNames = DataObjectAnnotator::config()->get('dbfield_tagnames');

        foreach ($fieldNames as $dbClass => $tagName) {
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
                $dataObjectName = $this->getAnnotationClassName($dataObjectName);
                $tagString = "{$dataObjectName} {$fieldName}()";

                $this->pushMethodTag($fieldName, $tagString);
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

                if ($dataObjectName === DataObject::class) {
                    $this->pushPropertyTag("string \${$fieldName}Class");
                }

                $dataObjectName = $this->getAnnotationClassName($dataObjectName);
                $tagString = "{$dataObjectName} {$fieldName}()";

                $this->pushMethodTag($fieldName, $tagString);
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
                $fieldName = trim($fieldName);
                // A many_many with a relation through another DataObject
                if (is_array($dataObjectName)) {
                    $dataObjectName = $dataObjectName['through'];
                }
                $dataObjectName = $this->resolveDotNotation($dataObjectName);
                $listName = $this->getAnnotationClassName($listType);
                $dataObjectName = $this->getAnnotationClassName($dataObjectName);

                if (DataObjectAnnotator::config()->get('use_collections_for_datalist')) {
                    $tagString = "{$listName}<{$dataObjectName}> {$fieldName}()";
                } else {
                    $tagString = "{$listName}|{$dataObjectName}[] {$fieldName}()";
                }

                $this->pushMethodTag($fieldName, $tagString);
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
