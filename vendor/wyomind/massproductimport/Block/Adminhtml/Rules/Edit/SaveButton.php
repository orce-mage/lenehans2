<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;

/**
 * Class SaveButton
 * @package Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = $this->getUrl('massproductimport/rules/save');

        return [
            'label' => __('Save rule'),
            'class' => 'save primary',
            'on_click' => "setLocation('" . $url . "')",
        ];
    }
}
