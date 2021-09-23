<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Observer\Backend;

use Amasty\Stockstatus\Model\Source\StockStatus;
use Amasty\Stockstatus\Model\StockstatusSettings\DeleteContext;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogEntityAttributeSaveBefore implements ObserverInterface
{
    /**
     * @var DeleteContext
     */
    private $deleteContext;

    public function __construct(
        DeleteContext $deleteContext
    ) {
        $this->deleteContext = $deleteContext;
    }

    /**
     * Observer for preload icons for OPTIONS of attribute custom_stock_status,
     * which should be deleted. Icons files deleted in after save observer.
     * @see \Amasty\Stockstatus\Observer\Backend\CatalogEntityAttributeSaveAfter
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $attribute = $observer->getData('data_object');

        if ($attribute && in_array($attribute->getAttributeCode(), [StockStatus::ATTIRUBTE_CODE])) {
            if ($optionsToUpdate = $attribute->getOption()) {
                $optionsToDelete = $optionsToUpdate['delete'] ?? [];
                $optionsToDelete = array_filter($optionsToDelete);

                foreach ($optionsToDelete as $optionId => $value) {
                    $this->deleteContext->registerEntities((int)$optionId);
                }
            }
        }
    }
}
