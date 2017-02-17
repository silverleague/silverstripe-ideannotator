<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

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
        "Captain"            => 'Axyr\IDEAnnotator\Tests\Player',
        'HasOneRelationship' => 'Axyr\IDEAnnotator\Tests\Player',
    );

    private static $has_many = array(
        'SubTeams' => 'Axyr\IDEAnnotator\Tests\SubTeam',
        'Comments' => 'Axyr\IDEAnnotator\Tests\TeamComment'
    );

    private static $many_many = array(
        'Players'           => 'Axyr\IDEAnnotator\Tests\Player.Players',
        'Reserves'          => 'Axyr\IDEAnnotator\Tests\Player.Reserves',
        'SecondarySubTeams' => 'Axyr\IDEAnnotator\Tests\SubTeam',
    );

    public function SecondarySubTeams()
    {

    }

}

Config::inst()->update('Axyr\IDEAnnotator\Tests\Team', 'extensions', array('Axyr\IDEAnnotator\Tests\Team_Extension'));
