<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class MoveIconDataToSettings
{
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('amasty_stockstatus_icon');

        if ($connection->isTableExists($tableName)) {
            try {
                $connection->beginTransaction();
                $dataToMove = array_map(
                    [$this, 'prepareData'],
                    $this->getIconsData($connection, $tableName)
                );

                if (!empty($dataToMove)) {
                    $connection->insertMultiple(
                        $setup->getTable(StockstatusSettingsInterface::MAIN_TABLE),
                        $dataToMove
                    );
                }
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }

            $connection->commit();
            $connection->dropTable($tableName);
        }
    }

    /**
     * Map Icon Data to StockstatusSettings
     *
     * @param array $iconData
     * @return array
     */
    private function prepareData(array $iconData): array
    {
        return [
            StockstatusSettingsInterface::STORE_ID => (int)$iconData['store_id'],
            StockstatusSettingsInterface::OPTION_ID => (int)$iconData['option_id'],
            StockstatusSettingsInterface::IMAGE_PATH => $iconData['path']
        ];
    }

    private function getIconsData(AdapterInterface $connection, string $tableName): array
    {
        $select = $connection->select();
        $select->from($tableName);
        $select->order('store_id ' . Select::SQL_ASC);

        return $connection->fetchAssoc($select);
    }
}
