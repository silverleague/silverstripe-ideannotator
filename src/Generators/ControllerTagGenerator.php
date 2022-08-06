<?php

namespace SilverLeague\IDEAnnotator\Generators;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use Page;
use ReflectionClass;

class ControllerTagGenerator extends AbstractTagGenerator
{
    /**
     * ControllerTagGenerator constructor.
     *
     * @param string $className
     * @param        $existingTags
     * @throws ReflectionException
     */
    public function __construct($className, $existingTags)
    {
        $this->mapPageTypesToControllerName();

        parent::__construct($className, $existingTags);
    }

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

            $this->pushPropertyTag(sprintf('%s dataRecord', $pageClassname));
            $this->pushMethodTag('data()', sprintf('%s data()', $pageClassname));

            // don't mixin Page, since this is a ContentController method
            if ($pageClassname !== 'Page') {
                $this->pushMixinTag($pageClassname);
            }
        } elseif ($this->isContentController($this->className) && array_key_exists($this->className, self::$pageClassesCache)) {
            $pageClassname = $this->getAnnotationClassName(self::$pageClassesCache[$this->className]);

            $this->pushPropertyTag(sprintf('%s dataRecord', $pageClassname));
            $this->pushMethodTag('data()', sprintf('%s data()', $pageClassname));

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

    /**
     * Generates the cache of Page types to Controllers when the controller_name config is used
     */
    protected function mapPageTypesToControllerName()
    {
        if (empty(self::$pageClassesCache)) {
            $pageClasses = ClassInfo::subclassesFor(Page::class);
            foreach ($pageClasses as $pageClassname) {
                $controllerName = Config::inst()->get($pageClassname, 'controller_name');
                if (!empty($controllerName)) {
                    self::$pageClassesCache[$controllerName] = $pageClassname;
                }
            }
        }
    }
}
