<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Block\Adminhtml\Rules\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public $module = "MassProductImport";

    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = [
            [
                'url' => $this->getUrl(strtolower($this->module) . '/rules/edit', ['id' => $row->getId()]),
                'caption' => __('Edit'),
            ],
            [
                'url' => $this->getUrl(strtolower($this->module) . '/rules/delete', ['id' => $row->getId()]),
                'caption' => __('Delete'),
            ]
        ];

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
