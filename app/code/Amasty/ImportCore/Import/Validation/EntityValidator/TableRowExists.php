<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\EntityValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Magento\Framework\App\ResourceConnection;

class TableRowExists implements FieldValidatorInterface
{
    const TABLE_NAME = 'tableName';
    const ID_FIELD_NAME = 'idFieldName';
    const CONNECTION_NAME = 'connectionName';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $config = [
        self::CONNECTION_NAME => ResourceConnection::DEFAULT_CONNECTION
    ];

    /**
     * @var array
     */
    private $validationResult = [];

    public function __construct(
        ResourceConnection $resourceConnection,
        array $config = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->config = array_merge($this->config, $config);

        if (empty($this->config[self::TABLE_NAME])) {
            throw new \LogicException('Table name is not specified for TableRowExists validator');
        }
        if (empty($this->config[self::ID_FIELD_NAME])) {
            throw new \LogicException('Id field name is not specified for TableRowExists validator');
        }
    }

    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field])) {
            $entityId = trim($row[$field]);

            if (!empty($entityId)) {
                if (!isset($this->validationResult[$entityId])) {
                    $this->validationResult[$entityId] = $this->isEntityExists($entityId);
                }

                return $this->validationResult[$entityId];
            }
        }

        return true;
    }

    /**
     * Check if entity exists
     *
     * @param int $entityId
     * @return bool
     */
    private function isEntityExists($entityId): bool
    {
        $connection = $this->resourceConnection->getConnection(
            $this->config[self::CONNECTION_NAME]
        );

        $tableName = $this->resourceConnection->getTableName($this->config[self::TABLE_NAME]);
        $idFieldName = $this->config[self::ID_FIELD_NAME];

        return (bool)$connection->fetchOne(
            $connection->select()
                ->from($tableName)
                ->where($idFieldName . ' = ?', $entityId)
                ->limit(1)
                ->columns([$idFieldName])
        );
    }
}
