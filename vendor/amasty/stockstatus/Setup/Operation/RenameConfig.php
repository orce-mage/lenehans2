<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Setup\Operation;

class RenameConfig
{
    private $changedSettings = 'amstockstatus/general/hide_default_status';
    private $resultSettings = 'amstockstatus/general/display_default_status';

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
            ->from($tableName, ['config_id', 'value'])
            ->where('path=?', $this->changedSettings);

        $settings = $connection->fetchAll($select);

        foreach ($settings as $row) {
            $connection->update(
                $tableName,
                ['path' => $this->resultSettings, 'value' => (int)!$row['value']],
                ['config_id = ?' => $row['config_id']]
            );
        }
    }
}
