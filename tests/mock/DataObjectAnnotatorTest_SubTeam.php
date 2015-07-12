<?php


class DataObjectAnnotatorTest_SubTeam extends DataObjectAnnotatorTest_Team implements TestOnly
{
    private static $db = array(
        'SubclassDatabaseField' => 'Varchar'
    );

    private static $has_one = array(
        "ParentTeam" => 'DataObjectAnnotatorTest_Team',
    );

    private static $many_many = array(
        'FormerPlayers' => 'DataObjectAnnotatorTest_Player'
    );

    private static $many_many_extraFields = array(
        'FormerPlayers' => array(
            'Position' => 'Varchar(100)'
        )
    );
}
