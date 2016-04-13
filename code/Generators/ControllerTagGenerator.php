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

    /**
     * Generate the mixins for Extensions.
     */
    protected function generateExtensionsTags()
    {
        if ($fields = (array)$this->getClassConfig('extensions')) {
            foreach ($fields as $fieldName) {
                $this->pushMixinTag($fieldName);
            }
        }
    }

    /**
     * Generate the Owner-properties for extensions.
     */
    protected function generateOwnerTags()
    {
        $owners = array();
        foreach ($this->extensionClasses as $class) {
            $config = Config::inst()->get($class, 'extensions', Config::UNINHERITED);
            if ($config !== null && in_array($this->className, $config, null)) {
                $owners[] = $class;
            }
        }
        if (count($owners)) {
            $owners[] = $this->className;
            $this->pushPropertyTag(implode("|", $owners) . " \$owner");
        }
    }
}
