<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Security\Member;

/**
 * Class Player
 */
class Player extends Member implements TestOnly
{
    private static $db = [
        'IsRetired'   => 'Boolean',
        'ShirtNumber' => 'Varchar',
        'Shirt'       => 'Varchar(10)'
    ];

    private static $belongs_to = [
        'CaptainTeam' => Team::class
    ];

    private static $has_one = [
        'FavouriteTeam' => Team::class,
    ];

    private static $belongs_many_many = [
        'TeamPlayer'  => 'SilverLeague\IDEAnnotator\Tests\Team.Team',
        'TeamReserve' => 'SilverLeague\IDEAnnotator\Tests\Team.Reserve'
    ];
}
