<?php

namespace SilverLeague\IDEAnnotator;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\Tag;
use SilverStripe\Control\Controller;

/**
 * Class DocBlockGenerator
 *
 * @package IDEAnnotator/Generators
 */
class DocBlockGenerator
{
    /**
     * The current class we are working with
     * @var string
     */
    protected $className = '';

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * @var AbstractTagGenerator
     */
    protected $tagGenerator;

    /**
     * DocBlockGenerator constructor.
     *
     * @param $className
     * @throws \ReflectionException
     */
    public function __construct($className)
    {
        $this->className = $className;
        $this->reflector = new \ReflectionClass($className);

        $generatorClass = $this->reflector->isSubclassOf(Controller::class)
            ? ControllerTagGenerator::class : OrmTagGenerator::class;

        $this->tagGenerator = new $generatorClass($className, $this->getExistingTags());
    }

    /**
     * @return Tag[]
     */
    public function getExistingTags()
    {
        $docBlock = $this->getExistingDocBlock();
        $docBlock = new DocBlock($docBlock);

        return $docBlock->getTags();
    }

    /**
     * Not that in case there are multiple doblocks for a class,
     * the last one will be returned
     *
     * If we file old style generated docblocks we remove them
     *
     * @return string
     */
    public function getExistingDocBlock()
    {
        return $this->reflector->getDocComment();
    }

    /**
     * @return DocBlock|string
     */
    public function getGeneratedDocBlock()
    {
        $docBlock = $this->getExistingDocBlock();

        return $this->mergeGeneratedTagsIntoDocBlock($docBlock);
    }

    /**
     * @param string $existingDocBlock
     * @return string
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function mergeGeneratedTagsIntoDocBlock($existingDocBlock)
    {
        $docBlock = new DocBlock($this->removeExistingSupportedTags($existingDocBlock));

        if (!$docBlock->getText()) {
            $docBlock->setText('Class \\' . $this->className);
        }

        foreach ($this->getGeneratedTags() as $tag) {
            $docBlock->appendTag($tag);
        }

        $serializer = new Serializer();
        $docBlock = $serializer->getDocComment($docBlock);

        return $docBlock;
    }

    /**
     * Remove all existing tags that are supported by this module.
     *
     * This will make sure that removed ORM properties and Extenions will not remain in the docblock,
     * while providing the option to manually add docblocks like @author etc.
     *
     * @param $docBlock
     * @return string
     */
    public function removeExistingSupportedTags($docBlock)
    {
        $replacements = [
            "/ \* @property ([\s\S]*?)\n/",
            "/ \* @method ([\s\S]*?)\n/",
            "/ \* @mixin ([\s\S]*?)\n/"
        ];

        return (string)preg_replace($replacements, '', $docBlock);
    }

    /**
     * @return Tag[]
     */
    public function getGeneratedTags()
    {
        return $this->tagGenerator->getTags();
    }

    /**
     * @return array
     */
    public function getExistingTagComments()
    {
        return $this->tagGenerator->getExistingTagComments();
    }
}
