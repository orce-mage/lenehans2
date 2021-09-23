<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute;

use Amasty\Stockstatus\Model\Source\StockStatus;

class EditPlugin
{
    /**
     * @param \Amasty\ShopbyBase\Block\Adminhtml\Catalog\Product\Attribute\Edit $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        $subject,
        string $result
    ): string {
        return $subject->getFilterCode() === 'attr_' . StockStatus::ATTIRUBTE_CODE ? '' : $result;
    }
}
