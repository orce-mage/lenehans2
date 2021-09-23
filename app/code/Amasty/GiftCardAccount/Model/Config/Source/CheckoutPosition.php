<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\Config\Source;

use Magento\Framework\Module\Manager;

class CheckoutPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    const CHECKOUT_DISCOUNTS = 0;
    const AMASTY_OSC_PAYMENT_BLOCK = 1;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [self::CHECKOUT_DISCOUNTS => __('Checkout Discounts')];

        if ($this->moduleManager->isEnabled('Amasty_Checkout')) {
            $result[self::AMASTY_OSC_PAYMENT_BLOCK] = __('Amasty Checkout Payment Methods Block');
        }

        return $result;
    }
}
