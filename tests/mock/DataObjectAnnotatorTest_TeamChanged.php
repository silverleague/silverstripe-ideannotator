<?php

namespace SilverLeague\IDEAnnotator\Tests;

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
 * @method \SilverLeague\IDEAnnotator\Tests\Player Captain() This is the Boss
 * @method \SilverLeague\IDEAnnotator\Tests\Player HasOneRelationship()
 * @method \SilverLeague\IDEAnnotator\Tests\Player ExtendedHasOneRelationship()
 * @method \SilverStripe\ORM\DataList|\SilverLeague\IDEAnnotator\Tests\SubTeam[] SubTeams()
 * @method \SilverStripe\ORM\DataList|DataObjectAnnotatorTest_TeamComment[] Comments()
 * @method \SilverStripe\ORM\ManyManyList|\SilverLeague\IDEAnnotator\Tests\Player[] Players()
 * @mixin \SilverLeague\IDEAnnotator\Tests\Team_Extension This adds extra methods
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
