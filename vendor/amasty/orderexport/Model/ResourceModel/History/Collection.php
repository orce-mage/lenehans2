<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * After load processing
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        // insert download buttons
        foreach ($this as &$row) {
            $filePath = BP . DIRECTORY_SEPARATOR . $row->getData('file_path');
            if ($row->getData('file_path')) {
                $row->setData('download_csv', '<a href="' . $filePath . '"> CSV\XML file </a>');
                $row->setData('download_xml', '<a href="' . $filePath . '.zip"> Archived file </a>');
            } else {
                $row->setData('download_csv', '-');
                $row->setData('download_xml', '-');
            }
        }

        return $this;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Orderexport\Model\History', 'Amasty\Orderexport\Model\ResourceModel\History');
    }
}
