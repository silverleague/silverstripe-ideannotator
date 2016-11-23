<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Core\Config\Config;

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
 * @method DataObjectAnnotatorTest_Player Captain() This is the Boss
 * @method DataObjectAnnotatorTest_Player HasOneRelationship()
 * @method DataObjectTest_Player ExtendedHasOneRelationship()
 * @method DataList|DataObjectAnnotatorTest_SubTeam[] SubTeams()
 * @method DataList|DataObjectAnnotatorTest_TeamComment[] Comments()
 * @method ManyManyList|DataObjectAnnotatorTest_Player[] Players()
 * @mixin DataObjectAnnotatorTest_Team_Extension This adds extra methods
 */
class DataObjectAnnotatorTest_TeamChanged extends DataObject implements TestOnly
{

    private static $db = array(
        'Title' => 'Varchar',
        'Price' => 'Currency'
    );

    private static $has_one = array(
        "Captain"            => 'DataObjectAnnotatorTest_Player',
        'HasOneRelationship' => 'DataObjectAnnotatorTest_Player',
    );

    private static $has_many = array(
        'SubTeams' => 'DataObjectAnnotatorTest_SubTeam',
        'Comments' => 'DataObjectAnnotatorTest_TeamComment'
    );

    private static $many_many = array(
        'Players'           => 'DataObjectAnnotatorTest_Player',
        'SecondarySubTeams' => 'DataObjectAnnotatorTest_SubTeam',
    );

    public function SecondarySubTeams()
    {

    }

}

Config::inst()->update('DataObjectAnnotatorTest_TeamChanged', 'extensions',
    array('DataObjectAnnotatorTest_Team_Extension'));
