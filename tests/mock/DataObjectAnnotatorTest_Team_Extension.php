<?php

use SilverStripe\ORM\DataExtension;
use SilverStripe\Dev\TestOnly;

class DataObjectAnnotatorTest_Team_Extension extends DataExtension implements TestOnly
{

    private static $db = array(
        'ExtendedVarcharField' => 'Varchar',
        'ExtendedIntField'     => 'Int'
    );

    private static $has_one = array(
        'ExtendedHasOneRelationship' => 'SilverStripe\\ORM\\Tests\\DataObjectTest\\DataObjectTest_Player'
    );
}
