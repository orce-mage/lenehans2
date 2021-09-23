<?php
namespace MageBig\SyntaxCms\Plugin\Widget\Model;

    /*
    * Copyright © 2016 SNM-Portal.com. All rights reserved.
    * See LICENSE.txt for license details.
    */

/**
 * Class Widget
 * @package MageBig\SyntaxCms\Plugin\Widget\Model
 */
class Widget
{
    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param $type
     * @param array $params
     * @param bool $asIs
     * @return array
     */
    public function beforeGetWidgetDeclaration(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Widget\Model\Widget $subject,
        $type,
        $params = [],
        $asIs = true
    ) {
        if ($type == 'MageBig\SyntaxCms\Block\Widget\Cm') {
            foreach ($params as $name => &$value) {
                if ($name == 'code') {
                    $value = str_replace(['{', '}', '"', "'"], ['\{', '\}', '\"', "\'"], $value);
                    $value = urlencode($value);
                    break;
                }
            }
        }
        return [$type, $params, $asIs];
    }

}