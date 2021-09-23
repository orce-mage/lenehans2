<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Block\Adminhtml\Buttons\Account;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndSendButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Save & Send Email'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'amgcard_account_formedit.areas',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['send' => 'email', 'back' => 'edit'],
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'on_click' => '',
            'sort_order' => 40
        ];
    }
}
