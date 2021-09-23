<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Block\Adminhtml\Buttons\Account;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CreateInBulkButton implements ButtonProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        return [
            'label' => __('Generate in Bulk'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'amgcard_account_listing.amgcard_account_listing.generate_in_bulk',
                                'actionName' => 'toggleModal'
                            ]
                        ]
                    ]
                ],
            ],
            'on_click' => '',
            'sort_order' => 5
        ];
    }
}
