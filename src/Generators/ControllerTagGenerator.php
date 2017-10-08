<?php

namespace SilverLeague\IDEAnnotator;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\ClassInfo;

class ControllerTagGenerator extends AbstractTagGenerator
{

    /**
     * @return void
     */
    protected function generateTags()
    {
        $this->generateControllerObjectTags();
        $this->generateExtensionsTags();
        $this->generateOwnerTags();
    }

    protected function generateControllerObjectTags()
    {
        $pageClassname = str_replace(['_Controller', 'Controller'], '', $this->className);
        if (class_exists($pageClassname) && $this->isContentController($this->className)) {
            $this->pushPropertyTag("\\$pageClassname" . ' dataRecord');
            $this->pushMethodTag($pageClassname, "\\$pageClassname" . ' data()');

            // don't mixin Page, since this is a ContentController method
            if ($pageClassname !== 'Page') {
                $this->pushMixinTag("\\$pageClassname");
            }
        }
    }

    /**
     * @param string $className
     * @return bool
     * @throws \ReflectionException
     */
    protected function isContentController($className)
    {
        $reflector = new \ReflectionClass($className);

        return ClassInfo::exists(ContentController::class)
            && $reflector->isSubclassOf(ContentController::class);
    }
}
