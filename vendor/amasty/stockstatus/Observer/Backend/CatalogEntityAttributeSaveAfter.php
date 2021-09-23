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

class CatalogEntityAttributeSaveAfter implements ObserverInterface
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
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $attribute = $observer->getData('data_object');

        if ($attribute && in_array($attribute->getAttributeCode(), [StockStatus::ATTIRUBTE_CODE])) {
            $this->deleteContext->flush();
        }
    }
}
