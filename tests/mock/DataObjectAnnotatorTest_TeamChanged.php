<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ManyManyList;
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
 * @method Player Captain() This is the Boss
 * @method Player HasOneRelationship()
 * @method Player ExtendedHasOneRelationship()
 * @method DataList|SubTeam[] SubTeams()
 * @method DataList|DataObjectAnnotatorTest_TeamComment[] Comments()
 * @method ManyManyList|Player[] Players()
 * @mixin Team_Extension This adds extra methods
 */
class TeamChanged extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
        'Price' => 'Currency'
    ];

    private static $has_one = [
        "Captain"            => Player::class,
        'HasOneRelationship' => Player::class,
    ];

    private static $has_many = [
        'SubTeams' => SubTeam::class,
        'Comments' => TeamComment::class
    ];

    private static $many_many = [
        'Players'           => Player::class,
        'SecondarySubTeams' => SubTeam::class,
    ];

    public function SecondarySubTeams()
    {
    }
}
