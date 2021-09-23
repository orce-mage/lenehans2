<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;

/**
 * Class BackButton
 * @package Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = $this->getUrl('massproductimport/rules/index');
        return [
            'label' => __('Back'),
            'class' => 'save ',
            'on_click' => "setLocation('" . $url . "')",

        ];
    }
}
