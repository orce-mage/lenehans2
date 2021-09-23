<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Sales;

use Magento\Framework\View\Element\Template;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver
     */
    private $gCardExtensionResolver;

    public function __construct(
        Template\Context $context,
        \Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver $gCardExtensionResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->gCardExtensionResolver = $gCardExtensionResolver;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Init totals block
     */
    public function initTotals()
    {
        $gCardExt = $this->gCardExtensionResolver->resolve($this->getSource());

        if (!$gCardExt || !$gCardExt->getGiftAmount()) {
            return;
        }

        $giftCard = new \Magento\Framework\DataObject(
            [
                'code' => 'amgiftcard',
                'value' => -$gCardExt->getGiftAmount(),
                'base_value' => -$gCardExt->getBaseGiftAmount(),
                'label' => __('Gift Cards')
            ]
        );

        $this->getParentBlock()->addTotalBefore($giftCard, 'grand_total');
    }
}
