<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;

class Thirdparty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_amorderexport_thirdparty', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|void
     * @throws LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();

        $tableName = $this->getTable($object->getData('table_name'));
        if (!$connection->isTableExists($tableName)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(__('Database table with specified name does not exist'))
            );
        }

        if (!$connection->tableColumnExists($tableName, $object->getData('join_field_reference'))) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(__('Column with specified name does not exist in the specified database table'))
            );
        }

        parent::_beforeSave($object);
    }
}
