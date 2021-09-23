<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\Condition;

use Magento\Rule\Model\Condition\Context;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->setType(\Amasty\GiftCard\Model\CodePool\Condition\Combine::class);
    }

    public function getNewChildSelectOptions()
    {
        return [
            [
                'value' => '',
                'label' => __('Please choose a condition to add')
            ],
            [
                'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                'label' => __('Product attribute combination'),
            ],
            [
                'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class,
                'label' => __('Products subselection')
            ],
            [
                'value' => \Amasty\GiftCard\Model\CodePool\Condition\Combine::class,
                'label' => __('Conditions combination')
            ]
        ];
    }
}
