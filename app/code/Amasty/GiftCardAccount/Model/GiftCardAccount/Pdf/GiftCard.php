<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class GiftCard extends DefaultTotal
{
    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver
     */
    private $gCardExtensionResolver;

    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver $gCardExtensionResolver,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->gCardExtensionResolver = $gCardExtensionResolver;
    }

    /**
     * @return float|int
     */
    public function getAmount()
    {
        $gCardExt = $this->gCardExtensionResolver->resolve($this->getSource());

        if (!$gCardExt) {
            return 0;
        }

        return -$gCardExt->getGiftAmount();
    }
}
