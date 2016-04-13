<?php


class ControllerTagGenerator extends AbstractTagGenerator
{

    /**
     * @return void
     */
    protected function generateTags()
    {
        $this->generatePageObjectTags();
        $this->generateExtensionsTags();
        $this->generateOwnerTags();
    }

    protected function generatePageObjectTags()
    {
        $pageClassname = str_replace("_Controller", "", $this->className);
        if(class_exists($pageClassname)) {
            $this->pushPropertyTag($pageClassname . ' dataRecord');
            $this->pushMethodTag($pageClassname, $pageClassname . ' data()');

            // don't mixin Page, since this is a ContentController method
            if($pageClassname !== 'Page') {
                $this->pushMixinTag($pageClassname);
            }
        }
    }
}
