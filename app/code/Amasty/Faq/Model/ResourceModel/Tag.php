<?php

namespace Amasty\Faq\Model\ResourceModel;

use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Setup\Operation;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Tag extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Operation\CreateTagTable::TABLE_NAME, TagInterface::TAG_ID);
    }

    /**
     * @param \Amasty\Faq\Api\Data\TagInterface[] $tags
     *
     * @return int[]
     */
    public function saveNoExistTags($tags)
    {
        $tagIds = [];
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (!$tag->getTagId()) {
                    $this->save($tag);
                }
                $tagIds[] = $tag->getTagId();
            }
        }

        return $tagIds;
    }
}
