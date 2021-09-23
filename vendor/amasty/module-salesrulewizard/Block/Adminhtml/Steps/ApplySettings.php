<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SalesRuleWizard
 */


namespace Amasty\SalesRuleWizard\Block\Adminhtml\Steps;

class ApplySettings extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        //Step 3
        return __('Rule Settings');
    }
}
