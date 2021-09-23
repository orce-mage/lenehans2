<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Renderer;

/**
 * Class Datetime
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Renderer
 */
class Datetime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime
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
        if ($row->getImportedAt() == "" || $row->getImportedAt() == "0000-00-00 00:00:00") {
            return "-";
        } else {
            $url = $this->getUrl(strtolower($this->module) . '/profiles/report', ['id' => $row->getId()]);
            return parent::render($row) . "&nbsp;&nbsp;<a href='javascript:void(require([\"wyomind_MassImportAndUpdate_profile\"], function (profile) {profile.report(\"" . $url . "\", " . $row->getId() . ", \"" . $row->getName() . "\", \"" . parent::render($row) . "\")}));'>" . __("Show report") . "</a>";
        }
    }
}
