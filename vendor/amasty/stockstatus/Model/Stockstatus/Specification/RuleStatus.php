<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Specification;

use Amasty\Stockstatus\Model\Rule\GetRuleForProduct;
use Magento\Catalog\Api\Data\ProductInterface;

class RuleStatus implements SpecificationInterface
{
    /**
     * @var GetRuleForProduct
     */
    private $getRuleForProduct;

    public function __construct(GetRuleForProduct $getRuleForProduct)
    {
        $this->getRuleForProduct = $getRuleForProduct;
    }

    public function resolve(ProductInterface $product): ?int
    {
        $appliedRule = $this->getRuleForProduct->execute((int) $product->getId(), $product->getStoreId());
        if ($appliedRule) {
            $product->getExtensionAttributes()->getStockstatusInformation()->setRuleId((int) $appliedRule->getId());
            $statusId = $appliedRule->getStockStatus();
        }

        return $statusId ?? null;
    }
}
