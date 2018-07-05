<?php

namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

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
