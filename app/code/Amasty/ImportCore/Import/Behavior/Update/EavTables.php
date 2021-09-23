<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Update;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\EavTables as EavTablesBehavior;
use Magento\Framework\DB\Select;

class EavTables extends EavTablesBehavior implements BehaviorInterface
{
    const TYPE_DELETE = 'delete';
    const TYPE_UPDATE = 'update';

    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();
        $preparedData = $this->attributeDataMapping($data);

        foreach ($preparedData as $tableName => $attributeData) {
            if ($tableName) {
                if (isset($attributeData[self::TYPE_UPDATE])) {
                    $this->resourceConnection->getConnection()
                        ->insertOnDuplicate($tableName, $attributeData['update']);
                }
                if (isset($attributeData[self::TYPE_DELETE])) {
                    $this->deleteAttributes($tableName, $attributeData['delete']);
                }
            }
        }

        return $result;
    }

    private function deleteAttributes(string $tableName, array $attributes)
    {
        $connection = $this->resourceConnection->getConnection();
        $conditions = [];
        foreach ($attributes as $attribute) {
            $conditionItem = [];
            foreach ($attribute as $field => $value) {
                $conditionItem[] = $connection->prepareSqlCondition(
                    $connection->quoteIdentifier($tableName . '.' . $field),
                    $value
                );
            }
            $conditions[] = '(' . implode(' ' . Select::SQL_AND . ' ', $conditionItem) . ')';
        }

        if (!empty($conditions)) {
            $where = implode(' ' . Select::SQL_OR . ' ', $conditions);
            $connection->delete($tableName, $where);
        }
    }

    private function attributeDataMapping(array $data): array
    {
        $resultData = [];
        if (!$this->linkField) {
            return $resultData;
        }

        foreach ($this->prepareAttributeValues($data) as $row) {
            $attributeSetId = isset($row['attribute_set_id']) ? (int)$row['attribute_set_id'] : null;
            $attributes = $this->keyByAttributeCode(
                $this->getEavAttributes(array_keys($row), $attributeSetId)
            );
            foreach ($row as $key => $value) {
                if (!isset($attributes[$key], $row[$this->linkField])
                    || $attributes[$key]->isStatic()
                ) {
                    continue;
                }

                $attribute = $attributes[$key];
                $tableName = $this->getEavTableName($attribute->getBackendType());
                $storeId = $this->getScopeValue($row);
                $attributeData = [
                    'attribute_id' => $attribute->getAttributeId(),
                    $this->linkField => (int)$row[$this->linkField]
                ];
                if ($storeId !== null) {
                    $attributeData['store_id'] = $storeId;
                }

                $actionType = null;
                if (!$attribute->isValueEmpty($value) || $attribute->isAllowedEmptyTextValue($value)) {
                    $attributeData['value'] = $value;
                    $actionType = self::TYPE_UPDATE;
                } else {
                    $actionType = self::TYPE_DELETE;
                }

                if ($actionType) {
                    $resultData[$tableName][$actionType][] = $attributeData;
                }
            }
        }

        return $resultData;
    }
}
