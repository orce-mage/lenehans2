<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Amasty\Stockstatus\Model\StockstatusSettings as StockstatusSettingsModel;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

class StockstatusSettings extends AbstractDb
{
    const USE_DEFAULT_POSTFIX = '_use_default';
    const DEFAULT_COLUMNS = [
        StockstatusSettingsInterface::ID,
        StockstatusSettingsInterface::OPTION_ID,
        StockstatusSettingsInterface::STORE_ID
    ];

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(StockstatusSettingsInterface::MAIN_TABLE, StockstatusSettingsInterface::ID);
    }

    /**
     * @param StockstatusSettingsModel $stockstatusSettings
     * @param int $optionId
     * @param int $storeId
     */
    public function loadByOptionIdAndStoreIdWithoutInheritance(
        StockstatusSettingsModel $stockstatusSettings,
        int $optionId,
        int $storeId
    ): void {
        $select = $this->_getLoadSelect(StockstatusSettingsInterface::OPTION_ID, $optionId, $stockstatusSettings);
        $select->where(
            $this->getConnection()->prepareSqlCondition(
                StockstatusSettingsInterface::STORE_ID,
                ['eq' => $storeId]
            )
        );
        $this->fillModelFromSelect($select, $stockstatusSettings, $storeId, $optionId);
    }

    public function loadByOptionIdAndStoreId(
        StockstatusSettingsModel $stockstatusSettings,
        int $optionId,
        int $storeId
    ): void {
        $select = $this->getLoadByOptionIdAndStoreIdSelect($optionId, $storeId);
        $this->fillModelFromSelect($select, $stockstatusSettings, $storeId, $optionId);
    }

    /**
     * Fix saving empty value for nullable fields
     *
     * @param DataObject $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(DataObject $object, $table)
    {
        $preparedData = parent::_prepareDataForTable($object, $table);

        if ($object->getStoreId()) {
            $nullableFields = $this->getNullableColumns();

            foreach ($nullableFields as $fieldName) {
                if (array_key_exists($fieldName, $preparedData)
                    && $object->getData($fieldName) === ''
                ) {
                    $preparedData[$fieldName] = '';
                }
            }
        }

        return $preparedData;
    }

    public function getNullableColumns(): array
    {
        $columnsInfo = $this->getConnection()->describeTable($this->getMainTable());

        return array_reduce($columnsInfo, function ($nullableColumns, $columnDescription): array {
            if ($columnDescription['NULLABLE'] ?? false) {
                $nullableColumns[] = $columnDescription['COLUMN_NAME'];
            }

            return $nullableColumns;
        }, []);
    }

    private function getLoadByOptionIdAndStoreIdSelect(int $optionId, int $storeId): Select
    {
        $select = $this->getConnection()->select();
        $mainTable = $this->getMainTable();
        $select->from(['default_store' => $mainTable]);
        $select->joinLeft(
            ['store_table' => $mainTable],
            sprintf(
                'default_store.%1$s = store_table.%1$s and default_store.%2$s = %3$d',
                StockstatusSettingsInterface::OPTION_ID,
                StockstatusSettingsInterface::STORE_ID,
                Store::DEFAULT_STORE_ID
            )
        );
        $select->where(sprintf(
            'store_table.%s = ?',
            StockstatusSettingsInterface::OPTION_ID
        ), $optionId);
        $select->where(sprintf(
            'store_table.%s in (?)',
            StockstatusSettingsInterface::STORE_ID
        ), [Store::DEFAULT_STORE_ID, $storeId]);
        $select->reset(Select::COLUMNS);
        $select->columns($this->getLoadByStoreIdColumns());
        $select->order(sprintf(
            'store_table.%s %s',
            StockstatusSettingsInterface::STORE_ID,
            Select::SQL_DESC
        ));

        return $select;
    }

    private function getLoadByStoreIdColumns(): array
    {
        $columns = [];

        foreach (self::DEFAULT_COLUMNS as $columnName) {
            $columns[$columnName] = "store_table.{$columnName}";
        }

        $columns = array_reduce($this->getNullableColumns(), function (array $columns, string $columnName): array {
            $ifnullCondition = sprintf('IFNULL (store_table.%1$s, default_store.%1$s)', $columnName);
            $useDefaultCondition = sprintf(
                'IF ((store_table.%s is null) and (store_table.%s != %d), 1, 0)',
                $columnName,
                StockstatusSettingsInterface::STORE_ID,
                Store::DEFAULT_STORE_ID
            );
            $columns[$columnName] = $ifnullCondition;
            $columns[$columnName . self::USE_DEFAULT_POSTFIX] = $useDefaultCondition;

            return $columns;
        }, $columns);

        return $columns;
    }

    private function fillModelFromSelect(
        Select $select,
        StockstatusSettingsModel $stockstatusSettings,
        int $storeId,
        int $optionId
    ): void {
        $data = $this->getConnection()->fetchRow($select);

        if (empty($data)) {
            $stockstatusSettings->setStoreId($storeId);
            $stockstatusSettings->setOptionId($optionId);
        } else {
            $stockstatusSettings->setData($data);
            $stockstatusSettings->setOrigData();
            $stockstatusSettings->setStoreId($storeId);
        }
    }
}
