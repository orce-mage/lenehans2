<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\GroupedProduct\Model\Product\Type;

use Amasty\Stockstatus\Model\Backend\UpdaterAttribute;
use Amasty\Stockstatus\Model\Source\StockStatus;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class GroupedPlugin
{
    public function afterGetAssociatedProductCollection(Grouped $subject, Collection $collection): Collection
    {
        $collection->addAttributeToSelect([StockStatus::ATTIRUBTE_CODE, UpdaterAttribute::EXPECTED_DATE_CODE]);

        return $collection;
    }
}
