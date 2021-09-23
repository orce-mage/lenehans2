<?php

namespace Wyomind\Framework\Plugin\Backend\Block;

use Wyomind\Framework\Helper\Module;

class Menu
{

    /**
     * @var Module
     */
    protected $module;

    /**
     * Menu constructor.
     * @param Module $module
     */
    public function __construct(
        Module $module
    ) {
    
        $this->module = $module;
    }


    public function aroundRenderNavigation($subject, $proceed, $menu, $level = 0, $limit = 0, $colBrakes = [])
    {
        $return = $proceed($menu, $level, $limit, $colBrakes);
        if ($level == 0) {
            $allExtensions = $this->module->getAllExtensions();
            foreach ($allExtensions['by_code'] as $code => $module) {
                $return = str_replace('item-discover-' . strtolower($code), 'item-discover-' . strtolower($code) . ' ' . $module['ribbon'], $return);
            }
        }
        return $return;

    }
}
