<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Wyomind\MassStockUpdate\Helper;

/**
 * Class Config
 * @package Wyomind\MassStockUpdate\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const SETTINGS_LOG = "massstockupdate/settings/log";
    /**
     *
     */
    const SETTINGS_NB_PREVIEW = "massstockupdate/settings/nb_preview";
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Framework\App\Helper\Context $context)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context);
    }
    /**
     * @return string
     */
    public function getSettingsLog()
    {
        return $this->_framework->getDefaultConfig(self::SETTINGS_LOG);
    }
    /**
     * @return string
     */
    public function getSettingsNbPreview()
    {
        return $this->_framework->getDefaultConfig(self::SETTINGS_NB_PREVIEW);
    }
}