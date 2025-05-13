<?php

namespace SilverLeague\IDEAnnotator\tests;

use ReflectionException;
use SilverLeague\IDEAnnotator\DataObjectAnnotator;
use SilverStripe\Dev\TestOnly;

/**
 * Class MockDataObjectAnnotator
 * Overload DataObjectAnnotator to make protected methods testable.
 * In this way we can just test the generated annotations without actually writing the files.
 */
class MockDataObjectAnnotator extends DataObjectAnnotator implements TestOnly
{

    /**
     * @param $fileContent
     * @param $className
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function getGeneratedFileContent($fileContent, $className)
    {
        return parent::getGeneratedFileContent($fileContent, $className);
    }
}
