<?php

class DataObjectAnnotatorTest_Team_Extension extends DataExtension implements TestOnly
{

    private static $db = array(
        'ExtendedVarcharField' => 'Varchar',
        'ExtendedIntField'     => 'Int'
    );

    private static $has_one = array(
        'ExtendedHasOneRelationship' => 'DataObjectTest_Player'
    );
}
