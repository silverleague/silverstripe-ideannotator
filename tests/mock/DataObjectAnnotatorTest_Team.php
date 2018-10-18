<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

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
        'Price'      => 'Currency',
        'Dudette'    => 'Varchar',
        'Dude'       => 'Varchar',
    ];

    private static $has_one = [
        'Captain'            => Player::class,
        'HasOneRelationship' => Player::class,
    ];

    private static $has_many = [
        'SubTeams'   => SubTeam::class,
        'Comments'   => TeamComment::class,
    ];

    private static $many_many = [
        'Players'           => 'SilverLeague\IDEAnnotator\Tests\Player.Players',
        'Reserves'          => 'SilverLeague\IDEAnnotator\Tests\Player.Reserves',
        'SecondarySubTeams' => SubTeam::class,
        'Supporters' => [
            'through' => TeamSupporter::class,
            'from'    => 'Team',
            'to'      => 'Supporter',
        ],
    ];

    public function SecondarySubTeams()
    {
    }
}
