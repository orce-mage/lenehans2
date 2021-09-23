<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Specification;

use Amasty\Stockstatus\Model\Range\GetProductQtyAdaptForRange;
use Amasty\Stockstatus\Model\Range\GetRangesForRuleAndQty;
use Amasty\Stockstatus\Model\Range\GetTargetStatusId;
use Amasty\Stockstatus\Model\Range\IsAvailableForProduct;
use Amasty\Stockstatus\Model\Rule\GetRuleForProduct;
use Magento\Catalog\Api\Data\ProductInterface;

class RangeStatus implements SpecificationInterface
{
    /**
     * @var GetRuleForProduct
     */
    private $getRuleForProduct;

    /**
     * @var GetRangesForRuleAndQty
     */
    private $getRangesForRuleAndQty;

    /**
     * @var GetTargetStatusId
     */
    private $getTargetStatusId;

    /**
     * @var GetProductQtyAdaptForRange
     */
    private $getProductQtyAdaptForRange;

    /**
     * @var IsAvailableForProduct
     */
    private $isAvailableForProduct;

    public function __construct(
        GetRuleForProduct $getRuleForProduct,
        GetRangesForRuleAndQty $getRangesForRuleAndQty,
        GetProductQtyAdaptForRange $getProductQtyAdaptForRange,
        GetTargetStatusId $getTargetStatusId,
        IsAvailableForProduct $isAvailableForProduct
    ) {
        $this->getRuleForProduct = $getRuleForProduct;
        $this->getRangesForRuleAndQty = $getRangesForRuleAndQty;
        $this->getTargetStatusId = $getTargetStatusId;
        $this->getProductQtyAdaptForRange = $getProductQtyAdaptForRange;
        $this->isAvailableForProduct = $isAvailableForProduct;
    }

    public function resolve(ProductInterface $product): ?int
    {
        if (!$this->isAvailableForProduct->execute($product)) {
            return null;
        }

        $appliedRule = $this->getRuleForProduct->execute((int) $product->getId(), $product->getStoreId());

        if ($appliedRule && $appliedRule->isActivateQtyRanges()) {
            $product->getExtensionAttributes()->getStockstatusInformation()->setRuleId((int) $appliedRule->getId());

            $ranges = $this->getRangesForRuleAndQty->execute(
                (int) $appliedRule->getId(),
                $this->getProductQtyAdaptForRange->execute($product)
            );
            $statusId = $this->getTargetStatusId->execute($ranges);
        }

        return $statusId ?? null;
    }
}
