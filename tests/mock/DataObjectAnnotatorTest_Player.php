<?php

/**
 * Class DataObjectAnnotatorTest_Player
 */
class DataObjectAnnotatorTest_Player extends Member implements TestOnly
{
    private static $db = array(
        'IsRetired'   => 'Boolean',
        'ShirtNumber' => 'Varchar',
    );

    private static $has_one = array(
        'FavouriteTeam' => 'DataObjectAnnotatorTest_Team',
    );

    private static $belongs_many_many = array(
        'Teams' => 'DataObjectAnnotatorTest_Team'
    );
}
