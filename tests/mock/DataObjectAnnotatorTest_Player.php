<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\Security\Member;
use SilverStripe\Dev\TestOnly;

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
        'FavouriteTeam' => 'Axyr\IDEAnnotator\Tests\Team',
    );

    private static $belongs_many_many = array(
        'TeamPlayer'  => 'Axyr\IDEAnnotator\Tests\Team.Team',
        'TeamReserve' => 'Axyr\IDEAnnotator\Tests\Team.Reserve'
    );
}
