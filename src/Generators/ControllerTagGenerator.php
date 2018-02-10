<?php

namespace SilverLeague\IDEAnnotator\Generators;

use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\ClassInfo;

class ControllerTagGenerator extends AbstractTagGenerator
{

    /**
     * @return void
     * @throws NotFoundExceptionInterface
     */
    protected function generateTags()
    {
        $this->generateControllerObjectTags();
        $this->generateExtensionsTags();
        $this->generateOwnerTags();
    }

    /**
     * Generate the controller tags, these differ slightly from the standard ORM tags
     *
     * @throws ReflectionException
     */
    protected function generateControllerObjectTags()
    {
        $pageClassname = str_replace(['_Controller', 'Controller'], '', $this->className);
        if (class_exists($pageClassname) && $this->isContentController($this->className)) {
            $pageClassname = $this->getAnnotationClassName($pageClassname);

            $this->pushPropertyTag($pageClassname . ' dataRecord');
            $this->pushMethodTag('data()', $pageClassname . ' data()');

            // don't mixin Page, since this is a ContentController method
            if ($pageClassname !== 'Page') {
                $this->pushMixinTag($pageClassname);
            }
        }
    }

    /**
     * @param string $className
     * @return bool
     * @throws ReflectionException
     */
    protected function isContentController($className)
    {
        $reflector = new ReflectionClass($className);

        return ClassInfo::exists(ContentController::class)
            && $reflector->isSubclassOf(ContentController::class);
    }
}
