<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Magento\Framework\App\ResourceConnection;

class DuplicateFieldChecker
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array $preparedIndexes
     */
    private $preparedIndexes;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function hasDuplicateFields(string $tableName, array $row): bool
    {
        return (bool)$this->getDuplicateRowId($tableName, $row);
    }

    public function getDuplicateRowId(string $tableName, array $row)
    {
        $connection = $this->resourceConnection->getConnection();
        $fullTableName = $this->resourceConnection->getTableName($tableName);
        if (!$connection->isTableExists($fullTableName)) {
            return false;
        }

        if (!isset($this->preparedIndexes[$fullTableName])) {
            $this->getPreparedIndexes($fullTableName);
        }
        if (empty($this->preparedIndexes[$fullTableName])) {
            return false;
        }
        $select = $connection->select()->from($fullTableName);
        foreach ($this->preparedIndexes[$fullTableName] as $key => $fields) {
            $andParts = [];
            foreach ($fields as $field) {
                if (empty($row[$field])) {
                    continue;
                }
                if (count($fields) > 1) {
                    $andParts[] = $connection->quoteInto($field . ' = ?', $row[$field]);
                } else {
                    $select->where($field . ' = ?', $row[$field]);
                }
            }
            if (empty($andParts)) {
                return false;
            }
            $select->orWhere(implode(' AND ', $andParts));
        }

        return $connection->fetchOne($select);
    }

    private function getPreparedIndexes(string $fullTableName): array
    {
        $indexes = [];
        $condition = sprintf('%s = 0 AND %s != "%s"', 'Non_unique', 'Key_name', 'PRIMARY');
        $indexesSql = sprintf('SHOW INDEXES FROM %s WHERE %s', $fullTableName, $condition);
        $connection = $this->resourceConnection->getConnection();

        $indexesDefinition = $connection->query($indexesSql)->fetchAll(\Zend_Db::FETCH_ASSOC);

        foreach ($indexesDefinition as $indexData) {
            if (isset($indexData['Key_name'], $indexData['Column_name'])) {
                $indexes[$indexData['Key_name']][] = $indexData['Column_name'];
            }
        }
        $this->preparedIndexes[$fullTableName] = $indexes;

        return $this->preparedIndexes[$fullTableName];
    }
}
