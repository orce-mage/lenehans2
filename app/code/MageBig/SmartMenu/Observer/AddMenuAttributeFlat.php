<?php

namespace MageBig\SmartMenu\Observer;

class AddMenuAttributeFlat implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getSelect()->columns([
            "include_in_menu",
            "smartmenu_show_on_cat",
            "smartmenu_cat_target",
            "smartmenu_cat_style",
            "smartmenu_cat_position",
            "smartmenu_cat_dropdown_width",
            "smartmenu_cat_column",
            "smartmenu_block_right",
            "smartmenu_block_left",
            "smartmenu_static_right",
            "smartmenu_static_left",
            "smartmenu_static_top",
            "smartmenu_static_bottom",
            "smartmenu_cat_label",
            "smartmenu_cat_icon",
            "smartmenu_cat_imgicon"
        ]);
    }
}