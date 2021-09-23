<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Delete;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\EavTables as EavTablesBehavior;

class EavTables extends EavTablesBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();

        $connection = $this->resourceConnection->getConnection();
        foreach ($data as $row) {
            if (!isset($row[$this->linkField])) {
                continue;
            }
            $attributeTypeMapping = $this->getAttributeTypeIdsMapping($row);
            if (!empty($attributeTypeMapping)) {
                foreach ($attributeTypeMapping as $tableName => $attributesIds) {
                    $connection->query(
                        $this->getSelect(
                            $tableName,
                            $attributesIds,
                            (int)$row[$this->linkField],
                            $this->getScopeValue($row)
                        )
                    );
                }
            }
        }

        return $result;
    }

    private function getSelect(string $tableName, array $attributeIds, int $entityId, ?int $scopeValue): string
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($tableName)
            ->where($connection->quoteInto($this->linkField . ' = ?', $entityId))
            ->where($connection->quoteInto('attribute_id IN (?)', $attributeIds));
        if ($scopeValue !== null) {
            $select->where($connection->quoteInto('store_id = ?', $scopeValue));
        }

        return $connection->deleteFromSelect($select, $tableName);
    }

    private function getAttributeTypeIdsMapping(array $row): array
    {
        $resultData = [];

        $attributeSetId = isset($row['attribute_set_id']) ? (int)$row['attribute_set_id'] : null;
        $attributes = $this->keyByAttributeCode(
            $this->getEavAttributes(array_keys($row), $attributeSetId)
        );
        foreach ($row as $key => $value) {
            if (!isset($attributes[$key])
                || $attributes[$key]->isStatic()
            ) {
                continue;
            }

            $attribute = $attributes[$key];
            $attributeType = $attribute->getBackendType();
            if ($tableName = $this->getEavTableName($attributeType)) {
                $resultData[$tableName]['attribute_ids'][] = (int)$attribute->getAttributeId();
            }
        }

        return $resultData;
    }
}
