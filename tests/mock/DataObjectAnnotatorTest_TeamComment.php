<?php

namespace Axyr\IDEAnnotator\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class TeamComment extends DataObject implements TestOnly
{
    private static $db = array(
        'Name'    => 'Varchar',
        'Comment' => 'Text'
    );

    private static $has_one = array(
        'Team' => 'Axyr\IDEAnnotator\Tests\Team'
    );

}
