<?php
namespace SilverLeague\IDEAnnotator\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class TeamSupporter extends DataObject implements TestOnly
{
    private static $db = [
        'Ranking' => 'Int',
    ];
    private static $has_one = [
        'Team'      => 'Team',
        'Supporter' => 'Supporter',
    ];
}
