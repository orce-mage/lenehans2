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
namespace Amasty\Orderexport\Block\Adminhtml\History\Column\Render\Download;

/**
 * Backup grid item renderer
 */
class File extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
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
        $filePath = BP . DIRECTORY_SEPARATOR . $row->getData('file_path');
        if ($filePath && file_exists($filePath)) {
            $link = '<a href="'
                    . $this->getUrl('amasty_orderexport/history/download/',['id'=>$row->getId()]). '">'
                    . __('Download File')
                    . '</a>';
        } else {
            $link = '- no file -';
        }

        return $link;
    }
}
