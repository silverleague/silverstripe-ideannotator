<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Dev\TestOnly;

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
