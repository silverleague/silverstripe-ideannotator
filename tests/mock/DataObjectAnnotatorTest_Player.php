<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Security\Member;
use SilverStripe\Dev\TestOnly;

/**
 * Class Player
 */
class Player extends Member implements TestOnly
{
    private static $db = [
        'IsRetired'   => 'Boolean',
        'ShirtNumber' => 'Varchar',
    ];

    private static $has_one = [
        'FavouriteTeam' => Team::class,
    ];

    private static $belongs_many_many = [
        'TeamPlayer'  => 'SilverLeague\IDEAnnotator\Tests\Team.Team',
        'TeamReserve' => 'SilverLeague\IDEAnnotator\Tests\Team.Reserve'
    ];
}
