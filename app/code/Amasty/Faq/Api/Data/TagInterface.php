<?php

namespace Amasty\Faq\Api\Data;

interface TagInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const TAG_ID = 'tag_id';
    const TITLE = 'title';
    /**#@-*/

    /**
     * @return int
     */
    public function getTagId();

    /**
     * @param int $tagId
     *
     * @return \Amasty\Faq\Api\Data\TagInterface
     */
    public function setTagId($tagId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Faq\Api\Data\TagInterface
     */
    public function setTitle($title);
}
