<?php


class DataObjectAnnotatorTest_TeamComment extends DataObject implements TestOnly
{
    private static $db = array(
        'Name' => 'Varchar',
        'Comment' => 'Text'
    );

    private static $has_one = array(
        'Team' => 'DataObjectAnnotatorTest_Team'
    );

}
