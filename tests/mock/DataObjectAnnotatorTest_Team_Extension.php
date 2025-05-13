<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class Team_Extension extends Extension implements TestOnly
{
    private static $db = array(
        'ExtendedVarcharField' => 'Varchar',
        'ExtendedIntField'     => 'Int'
    );

    private static $has_one = array(
        'ExtendedHasOneRelationship' => Player::class
    );
}
