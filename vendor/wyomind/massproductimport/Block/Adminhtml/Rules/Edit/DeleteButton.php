<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;
/**
 * Class DeleteButton
 * @package Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function __construct(\Wyomind\MassProductImport\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry);
    }
    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = $this->getUrl('massproductimport/rules/delete', ["id" => $this->request->getParam("id")]);
        return ['label' => __('Delete rule'), 'class' => 'delete primary', 'on_click' => "setLocation('" . $url . "')"];
    }
}