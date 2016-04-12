<?php

/**
 *
 */

/* comment */

// Another comment
class DataObjectAnnotatorTest_Team extends DataObject implements TestOnly
{

    private static $db = array(
        'Title'      => 'Varchar',
        'VisitCount' => 'Int'
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
        'Players' => 'DataObjectAnnotatorTest_Player'
    );
}

Config::inst()->update('DataObjectAnnotatorTest_Team', 'extensions', array('DataObjectAnnotatorTest_Team_Extension'));
