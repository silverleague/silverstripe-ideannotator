<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Member;

/**
 * Class Player
 */
class Player extends Member implements TestOnly
{
    private static $db = array(
        'IsRetired'   => 'Boolean',
        'ShirtNumber' => 'Varchar',
    );

    private static $has_one = array(
        'FavouriteTeam' => 'IDEAnnotator\Tests\Team',
    );

    private static $belongs_many_many = array(
        'TeamPlayer'  => 'IDEAnnotator\Tests\Team.Team',
        'TeamReserve' => 'IDEAnnotator\Tests\Team.Reserve'
    );
}
