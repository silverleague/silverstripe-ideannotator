<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 *
 */

/* comment */

// Another comment
class Team extends DataObject implements TestOnly
{

    private static $db = array(
        'Title'      => 'Varchar',
        'VisitCount' => 'Int',
        'Price'      => 'Currency'
    );

    private static $has_one = array(
        "Captain"            => 'IDEAnnotator\Tests\Player',
        'HasOneRelationship' => 'IDEAnnotator\Tests\Player',
    );

    private static $has_many = array(
        'SubTeams' => 'IDEAnnotator\Tests\SubTeam',
        'Comments' => 'IDEAnnotator\Tests\TeamComment'
    );

    private static $many_many = array(
        'Players'           => 'IDEAnnotator\Tests\Player.Players',
        'Reserves'          => 'IDEAnnotator\Tests\Player.Reserves',
        'SecondarySubTeams' => 'IDEAnnotator\Tests\SubTeam',
    );

    public function SecondarySubTeams()
    {

    }

}

Config::inst()->update('IDEAnnotator\Tests\Team', 'extensions', array('IDEAnnotator\Tests\Team_Extension'));
