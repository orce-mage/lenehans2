<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Orderexport\Block\Adminhtml\History\Column\Render;

/**
 * Backup grid item renderer
 */
class FileSize extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        if ($row->getData('file_size') > 0) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            $bytes = max($row->getData('file_size'), 0);
            $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow   = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);

            return round($bytes, 2) . ' ' . $units[$pow];
        } else {
            $link = '- no file -';
        }

        return $link;
    }
}
