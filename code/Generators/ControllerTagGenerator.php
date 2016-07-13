<?php


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
        $pageClassname = str_replace("_Controller", "", $this->className);
        if(class_exists($pageClassname) && $this->isContentController($this->className)) {
            $this->pushPropertyTag($pageClassname . ' dataRecord');
            $this->pushMethodTag($pageClassname, $pageClassname . ' data()');

            // don't mixin Page, since this is a ContentController method
            if($pageClassname !== 'Page') {
                $this->pushMixinTag($pageClassname);
            }
        }
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function isContentController($className)
    {
        $reflector = new ReflectionClass($className);
        return SS_ClassLoader::instance()->classExists('ContentController') && $reflector->isSubclassOf('ContentController');
    }
}
