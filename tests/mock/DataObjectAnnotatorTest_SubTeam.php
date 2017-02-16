<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;

class SubTeam extends Team implements TestOnly
{
    private static $db = array(
        'SubclassDatabaseField' => 'Varchar'
    );

    private static $has_one = array(
        "ParentTeam" => 'IDEAnnotator\Tests\Team',
    );

    private static $many_many = array(
        'FormerPlayers' => 'IDEAnnotator\Tests\Player'
    );

    private static $many_many_extraFields = array(
        'FormerPlayers' => array(
            'Position' => 'Varchar(100)'
        )
    );
}
