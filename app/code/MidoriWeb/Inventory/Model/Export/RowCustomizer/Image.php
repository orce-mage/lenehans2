<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace MidoriWeb\Inventory\Model\Export\RowCustomizer;

use Magento\Framework\UrlInterface;

/**
 * Class Image
 */
class Image extends \Amasty\Feed\Model\Export\RowCustomizer\Image
{

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($storeManager);
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product/';
    }

}
