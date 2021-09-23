<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SalesRuleWizard
 */


namespace Amasty\SalesRuleWizard\Plugin\SalesRule\Block\Adminhtml\Promo;

class QuotePlugin
{
    /**
     * @param \Magento\SalesRule\Block\Adminhtml\Promo\Quote $subject
     */
    public function beforeSetLayout(\Magento\SalesRule\Block\Adminhtml\Promo\Quote $subject)
    {
        $subject->addButton(
            'addWizard',
            [
                'label' => __('Free Gift Rules Wizard'),
                'onclick' => 'setLocation(\'' . $subject->getUrl('amasty_promowizard/wizard') . '\')',
                'class' => 'add primary'
            ]
        );
    }
}
