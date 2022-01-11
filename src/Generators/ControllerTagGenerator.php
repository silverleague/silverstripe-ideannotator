<?php

namespace SilverLeague\IDEAnnotator\Generators;

use Page;
use ReflectionClass;
use ReflectionException;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;

class ControllerTagGenerator extends AbstractTagGenerator
{
    private static $pageClassesCache = [];

    /**
     * @return void
     * @throws ReflectionException
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
        } else if ($this->isContentController($this->className)) {
            if (empty(self::$pageClassesCache)) {
                self::$pageClassesCache = ClassInfo::subclassesFor(Page::class);
            }

            foreach (self::$pageClassesCache as $pageClassname) {
                if (Config::inst()->get($pageClassname, 'controller_name') == $this->className) {
                    $pageClassname = $this->getAnnotationClassName($pageClassname);

                    $this->pushPropertyTag($pageClassname . ' dataRecord');
                    $this->pushMethodTag('data()', $pageClassname . ' data()');

                    // don't mixin Page, since this is a ContentController method
                    if ($pageClassname !== 'Page') {
                        $this->pushMixinTag($pageClassname);
                    }

                    break;
                }
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
