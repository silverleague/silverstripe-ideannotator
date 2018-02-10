<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataExtension;

class Team_Extension extends DataExtension implements TestOnly
{
    private static $db = array(
        'ExtendedVarcharField' => 'Varchar',
        'ExtendedIntField'     => 'Int'
    );

    private static $has_one = array(
        'ExtendedHasOneRelationship' => Player::class
    );
}
