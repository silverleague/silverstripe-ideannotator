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

        $generatorClass = $this->reflector->isSubclassOf('ContentController')
                        ? 'ControllerTagGenerator' : 'OrmTagGenerator';

        $this->tagGenerator = new $generatorClass($className);
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
     * @return DocBlock\Tag[]
     */
    public function getGeneratedTags()
    {
        return $this->tagGenerator->getTags();
    }

    /**
     * @return array
     */
    public function getTagsMergedWithExisting()
    {
        /**
         * set array keys so we can match existing with generated tags
         */
        $existing = $this->tagGenerator->getSupportedTagTypes();
        foreach($this->getExistingTags() as $tag) {
            $content = $tag->getContent();
            if($tag->getName() === 'property') {
                $existing['properties'][$content] = new Tag($tag->getName(), $content);
            }elseif($tag->getName() === 'method') {
                $existing['methods'][$content] = new Tag($tag->getName(), $content);
            }elseif($tag->getName() === 'mixin') {
                $existing['mixins'][$content] = new Tag($tag->getName(), $content);
            }else{
                $existing['other'][$content] = new Tag($tag->getName(), $content);
            }
        }

        /**
         * Remove the generated tags that already exist
         */
        $tags = (array)$this->tagGenerator->getTags();
        foreach ($tags as $tagType => $tagList) {
            foreach((array)$tagList as $type => $tag) {
                $content = $tag->getContent();
                if(isset($existing[$tagType][$content])) {
                    unset($tags[$tagType][$content]);
                }
            }
        }

        return $tags;
    }

    /**
     * @param string $existingDocBlock
     * @return string
     */
    protected function mergeGeneratedTagsIntoDocBlock($existingDocBlock)
    {
        $docBlock = new DocBlock($existingDocBlock);

        if (!$docBlock->getText()) {
            $docBlock->setText('Class ' . $this->className);
        }

        foreach($this->getTagsMergedWithExisting() as $tags) {
            foreach($tags as $tag) {
                $docBlock->appendTag($tag);
            }
        }

        $serializer = new DocBlockSerializer();
        $docBlock = $serializer->getDocComment($docBlock);

        return $docBlock;
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
