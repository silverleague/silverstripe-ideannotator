<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class TeamComment extends DataObject implements TestOnly
{
    private static $db = [
        'Name'    => 'Varchar',
        'Comment' => 'Text'
    ];

    private static $has_one = [
        'Team' => Team::class
    ];
}
