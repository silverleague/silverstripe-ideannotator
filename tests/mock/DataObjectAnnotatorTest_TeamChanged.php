<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * Class DataObjectAnnotatorTest_Team
 *
 * @author Simon
 * @property string $Title The Team Name
 * @property int $VisitCount
 * @property string $ExtendedVarcharField
 * @property int $ExtendedIntField
 * @property int $CaptainID
 * @property int $HasOneRelationshipID
 * @property int $ExtendedHasOneRelationshipID
 * @method \IDEAnnotator\Tests\Player Captain() This is the Boss
 * @method \IDEAnnotator\Tests\Player HasOneRelationship()
 * @method \IDEAnnotator\Tests\Player ExtendedHasOneRelationship()
 * @method \SilverStripe\ORM\DataList|\IDEAnnotator\Tests\SubTeam[] SubTeams()
 * @method \SilverStripe\ORM\DataList|DataObjectAnnotatorTest_TeamComment[] Comments()
 * @method \SilverStripe\ORM\ManyManyList|\IDEAnnotator\Tests\Player[] Players()
 * @mixin \IDEAnnotator\Tests\Team_Extension This adds extra methods
 */
class TeamChanged extends DataObject implements TestOnly
{

    private static $db = array(
        'Title' => 'Varchar',
        'Price' => 'Currency'
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
        'Players'           => 'IDEAnnotator\Tests\Player',
        'SecondarySubTeams' => 'IDEAnnotator\Tests\SubTeam',
    );

    public function SecondarySubTeams()
    {

    }

}

Config::inst()->update('IDEAnnotator\Tests\TeamChanged', 'extensions', array('IDEAnnotator\Tests\Team_Extension'));
