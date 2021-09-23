<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

use \Magento\Framework\Component\ComponentRegistrar;

$themeDir       = array_filter(glob(__DIR__ . '/*'), 'is_dir');
$parentTheme    = 'layout01';
$parentThemeDir = __DIR__ . '/' . $parentTheme;
if (($key = array_search($parentThemeDir, $themeDir)) !== false) {
    unset($themeDir[$key]);
}
array_unshift($themeDir, $parentThemeDir);

$temp = explode('/', __DIR__);
if (count($temp) == 1) {
    $temp = explode('\\', __DIR__);
}
$package = end($temp);
foreach ($themeDir as $dir) {
    $temp2 = explode('/', $dir);
    $theme = end($temp2);
    ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::THEME,
        'frontend/MageBig/' . $package . '_' . $theme,
        $dir
    );
}
