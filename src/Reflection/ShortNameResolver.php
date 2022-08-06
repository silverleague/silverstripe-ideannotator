<?php

namespace SilverLeague\IDEAnnotator\Reflection;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;

class ShortNameResolver extends FqsenResolver
{
    public function resolve(string $fqsen, ?Context $context = null): Fqsen
    {
        if ($context === null) {
            $context = new Context('');
        }

        return new Fqsen($fqsen);
    }
}
