<?php

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Serializer as DocBlockSerializer;

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
     * @var ReflectionClass
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
     */
    public function __construct($className)
    {
        $this->className    = $className;
        $this->reflector    = new ReflectionClass($className);

        $generatorClass = $this->reflector->isSubclassOf('SilverStripe\\Control\\Controller')
                        ? 'ControllerTagGenerator' : 'OrmTagGenerator';

        $this->tagGenerator = new $generatorClass($className, $this->getExistingTags());
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
        $existing = $this->getExistingDocBlock();
        $docBlock = $this->removeOldStyleDocBlock($existing);
        return $this->mergeGeneratedTagsIntoDocBlock($docBlock);
    }

    /**
     * @return DocBlock\Tag[]
     */
    public function getExistingTags()
    {
        $existing = $this->getExistingDocBlock();
        $docBlock = $this->removeOldStyleDocBlock($existing);
        $docBlock = new DocBlock($docBlock);
        return $docBlock->getTags();
    }

    /**
     * @return array
     */
    public function getExistingTagComments()
    {
        return $this->tagGenerator->getExistingTagComments();
    }

    /**
     * @return DocBlock\Tag[]
     */
    public function getGeneratedTags()
    {
        return $this->tagGenerator->getTags();
    }

    /**
     * @param string $existingDocBlock
     * @return string
     */
    protected function mergeGeneratedTagsIntoDocBlock($existingDocBlock)
    {
        $docBlock = new DocBlock($this->removeExistingSupportedTags($existingDocBlock));

        if (!$docBlock->getText()) {
            $docBlock->setText('Class ' . $this->className);
        }

        foreach($this->getGeneratedTags() as $tag) {
            $docBlock->appendTag($tag);
        }

        $serializer = new DocBlockSerializer();
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
        $replacements = array(
            "/ \* @property ([\s\S]*?)\n/",
            "/ \* @method ([\s\S]*?)\n/",
            "/ \* @mixin ([\s\S]*?)\n/"
        );

        return (string)preg_replace($replacements, '', $docBlock);
    }

    /**
     * Removes the unnecessary STARTTAG and ENDTAG
     * If they are left behind somehow
     *
     * @param $docBlock
     *
     * @return mixed
     */
    protected function removeOldStyleDocBlock($docBlock)
    {
        $startTag = DataObjectAnnotator::STARTTAG;
        $endTag = DataObjectAnnotator::ENDTAG;

        /**
         * First remove the complete generated docblock
         */
        $docBlock = preg_replace("/\/\*\*\n \* $startTag([\s\S]*?) \* $endTag\n \*\//", "\n", $docBlock);

        /**
         * Then remove the start and end tag seperate, if it still exists in case a developer has merged
         * the generated docblocks with existing ones.
         */
        $docBlock = preg_replace(array("/ \* $startTag\n/", "/ \* $endTag\n/"), '', $docBlock);

        return $docBlock;
    }
}
