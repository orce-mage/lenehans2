<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Image\ResourceModel;

use Amasty\GiftCard\Api\Data\ImageInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Image extends AbstractDb
{
    const TABLE_NAME = 'amasty_giftcard_image';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ImageInterface::IMAGE_ID);
    }
}
