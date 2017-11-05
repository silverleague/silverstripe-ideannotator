<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 *
 */

/* comment */

// Another comment
class Team extends DataObject implements TestOnly
{
    private static $extensions = [
        Team_Extension::class
    ];

    private static $db = [
        'Title'      => 'Varchar',
        'VisitCount' => 'Int',
        'Price'      => 'Currency'
    ];

    private static $has_one = [
        'Captain'            => Player::class,
        'HasOneRelationship' => Player::class,
    ];

    private static $has_many = [
        'SubTeams' => SubTeam::class,
        'Comments' => TeamComment::class
    ];

    private static $many_many = [
        'Players'           => 'SilverLeague\IDEAnnotator\Tests\Player.Players',
        'Reserves'          => 'SilverLeague\IDEAnnotator\Tests\Player.Reserves',
        'SecondarySubTeams' => SubTeam::class,
    ];

    public function SecondarySubTeams()
    {
    }
}
