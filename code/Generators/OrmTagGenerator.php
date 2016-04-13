<?php

/**
 * OrmTagGenerator
 *
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
    protected function generateBelongsToTags()
    {
        if ($fields = (array)$this->getClassConfig('belongs_to')) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->pushMethodTag($fieldName, $dataObjectName . " \$$fieldName");
            }
        }
    }

    /**
     * Generate the $has_one property and method values.
     */
    protected function generateHasOneTags()
    {
        if ($fields = (array)$this->getClassConfig('has_one')) {
            foreach ($fields as $fieldName => $dataObjectName) {
                $this->pushPropertyTag("int \${$fieldName}ID");
                $this->pushMethodTag($fieldName, "{$dataObjectName} {$fieldName}()");
            }
        }
    }

    /**
     * Generate the $has_many method values.
     */
    protected function generateHasManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('has_many'), 'DataList');
    }

    /**
     * Generate the $many_many method values.
     */
    protected function generateManyManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('many_many'), 'ManyManyList');
    }

    /**
     * Generate the $belongs_many_many method values.
     */
    protected function generateBelongsManyManyTags()
    {
        $this->generateTagsForDataLists($this->getClassConfig('belongs_many_many'), 'ManyManyList');
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
}
