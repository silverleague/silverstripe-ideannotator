<?php

namespace IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class TeamComment extends DataObject implements TestOnly
{
    private static $db = array(
        'Name'    => 'Varchar',
        'Comment' => 'Text'
    );

    private static $has_one = array(
        'Team' => 'IDEAnnotator\Tests\Team'
    );

}
