<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

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
 * @method \Axyr\IDEAnnotator\Tests\Player Captain() This is the Boss
 * @method \Axyr\IDEAnnotator\Tests\Player HasOneRelationship()
 * @method \Axyr\IDEAnnotator\Tests\Player ExtendedHasOneRelationship()
 * @method \SilverStripe\ORM\DataList|\Axyr\IDEAnnotator\Tests\SubTeam[] SubTeams()
 * @method \SilverStripe\ORM\DataList|DataObjectAnnotatorTest_TeamComment[] Comments()
 * @method \SilverStripe\ORM\ManyManyList|\Axyr\IDEAnnotator\Tests\Player[] Players()
 * @mixin \Axyr\IDEAnnotator\Tests\Team_Extension This adds extra methods
 */
class TeamChanged extends DataObject implements TestOnly
{

    private static $db = array(
        'Title' => 'Varchar',
        'Price' => 'Currency'
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
        'Players'           => 'Axyr\IDEAnnotator\Tests\Player',
        'SecondarySubTeams' => 'Axyr\IDEAnnotator\Tests\SubTeam',
    );

    public function SecondarySubTeams()
    {

    }

}

Config::inst()->update('Axyr\IDEAnnotator\Tests\TeamChanged', 'extensions', array('Axyr\IDEAnnotator\Tests\Team_Extension'));
