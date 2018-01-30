<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;

class SubTeam extends Team implements TestOnly
{
    private static $db = [
        'SubclassDatabaseField' => 'Varchar'
    ];

    private static $has_one = [
        "ParentTeam" => Team::class,
    ];

    private static $many_many = [
        'FormerPlayers' => Player::class
    ];

    private static $many_many_extraFields = [
        'FormerPlayers' => [
            'Position' => 'Varchar(100)'
        ]
    ];
}
