<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\TagInterface;
use Magento\Framework\Model\AbstractModel;

class Tag extends AbstractModel implements TagInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Faq\Model\ResourceModel\Tag::class);
        $this->setIdFieldName('tag_id');
    }

    /**
     * @inheritdoc
     */
    public function getTagId()
    {
        return $this->_getData(TagInterface::TAG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTagId($tagId)
    {
        $this->setData(TagInterface::TAG_ID, $tagId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_getData(TagInterface::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->setData(TagInterface::TITLE, $title);

        return $this;
    }
}
