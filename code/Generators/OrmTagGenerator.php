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
     * Default tagname is will be @string .
     *
     * All exceptions for @see DBField types are listed here
     *
     * @see generateDBTags();
     * @var array
     */
    protected static $dbfield_tagnames = array(
        'Int'     => 'int',
        'DBInt'   => 'int',
        'Boolean' => 'boolean',
        'Float'   => 'float',
        'DBFloat' => 'float',
        'Decimal' => 'float'
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

                $fieldObj = Object::create_from_string($dataObjectName);

                foreach(self::$dbfield_tagnames as $dbClass => $tagName) {
                    if(class_exists($dbClass)) {
                        $obj = Object::create_from_string($dbClass);
                        if($fieldObj instanceof $obj) {
                            $prop = $tagName;
                        }
                    }
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
