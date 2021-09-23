<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Source;

class GiftCardCodePool extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->collectionFactory->create()->toOptionArray();
    }
}
