<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\ResourceModel;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCard\Model\Image\Image::class,
            \Amasty\GiftCard\Model\Image\ResourceModel\Image::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return Collection
     */
    public function showOnlyAdminUpload(): Collection
    {
        return $this->addFieldToFilter(
            ImageInterface::IS_USER_UPLOAD,
            false
        );
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray(ImageInterface::IMAGE_ID, ImageInterface::TITLE);
    }
}
