<?php


use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class RootTeam extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar(255)',
    ];
}
