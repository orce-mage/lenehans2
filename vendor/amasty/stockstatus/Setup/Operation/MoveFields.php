<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Setup\Operation;

class MoveFields
{
    private $changedConfigureproduct = [
        '"amstockstatus/general/stockalert"',
        '"amstockstatus/general/outofstock"',
        '"amstockstatus/general/change_custom_configurable_status"',
    ];

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(\Magento\Framework\Setup\SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('core_config_data');

        $select = $setup->getConnection()->select()
            ->from($tableName, ['config_id','path'])
            ->where('path IN (' . implode(',', $this->changedConfigureproduct) . ')');

        $settings = $connection->fetchPairs($select);

        foreach ($settings as $key => $value) {
            $value = str_replace('general', 'configurable_products', $value);
            $connection->update($tableName, ['path' => $value], ['config_id = ?' => $key]);
        }
    }
}
