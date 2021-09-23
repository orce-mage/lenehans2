<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Gallery
 */
class Gallery extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceModel\Gallery::class);
    }
}
