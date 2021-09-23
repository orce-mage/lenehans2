<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Renderer;

/**
 * Class Action
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var string
     */
    public $module = "MassStockUpdate";

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = [
            [
                'url' => $this->getUrl(strtolower($this->module) . '/profiles/edit', ['id' => $row->getId()]),
                'caption' => __('Edit'),
            ],
            [
                'url' => "javascript:void(require(['wyomind_MassImportAndUpdate_profile'], function (profile) { profile.delete('" . $this->getUrl(strtolower($this->module) . '/profiles/delete', ['id' => $row->getId()]) . "')}))",
                'caption' => __('Delete'),
            ],
            [
                'url' => "javascript:void(require(['wyomind_MassImportAndUpdate_profile'], function (profile) {profile.run('" . $this->getUrl(strtolower($this->module) . '/profiles/run', ['id' => $row->getId()]) . "')}))",
                'caption' => __('Run profile'),
            ]
        ];
        if ($row->getImportedAt() != "" && $row->getImportedAt() != "0000-00-00 00:00:00") {
            $actions[] = [
                'url' => "javascript:void(require(['wyomind_MassImportAndUpdate_profile'], function (profile) {profile.report('" . $this->getUrl(strtolower($this->module) . '/profiles/report', ['id' => $row->getId()]) . "', '" . $row->getId() . "', '" . $row->getName() . "', '" . $row->getImportedAt() . "')}))",
                "caption" => __("Show report")
            ];
        }
        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
