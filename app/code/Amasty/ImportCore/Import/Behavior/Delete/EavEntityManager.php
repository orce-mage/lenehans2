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
use Amasty\ImportCore\Import\Behavior\EavEntityManager as EavEntityManagerBehavior;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\EntityManager\Operation\AttributeInterface as OperationAttributeInterface;

class EavEntityManager extends EavEntityManagerBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();

        /** @var OperationAttributeInterface[] $actions */
        $actions = $this->attributePool->getActions($this->entityType, 'update');
        foreach ($this->prepareAttributeValuesForDelete($data) as $row) {
            $this->applyActions($actions, $row);
        }

        return $result;
    }

    /**
     * Prepare attribute values for delete
     *
     * @param array $attrValues
     * @return array
     */
    private function prepareAttributeValuesForDelete(array $attrValues): array
    {
        $attrValues = $this->prepareAttributeValues($attrValues);
        if (!count($attrValues)) {
            return $attrValues;
        }

        if ($this->isScoped()) {
            $scopeIdentifier = $this->getScopeIdentifier();
            if (count($attrValues) == 1 && $attrValues[0][$scopeIdentifier] == 0) {
                $rowsByAllScopes = [];
                $scopeCodes = $this->getScopeCodes($this->getScopeType());
                foreach ($scopeCodes as $scopeCode) {
                    $rowsByAllScopes[] = [$scopeIdentifier => $scopeCode];
                }

                return $this->fillNull($rowsByAllScopes, $attrValues[0]);
            }
        }

        return $this->fillNull($attrValues);
    }

    /**
     * Fills null values for attributes that should be deleted
     *
     * @param array $rows
     * @param array|null $pattern
     * @return array
     */
    private function fillNull(array $rows, array $pattern = null): array
    {
        $attributes = $this->attributeLoader->getAttributes(
            $this->entityType,
            $attrValues[0]['attribute_set_id'] ?? null
        );

        $mapAttrCodesCallback = function (AttributeInterface $attribute) {
            return $attribute->getAttributeCode();
        };
        $attributeCodes = array_map($mapAttrCodesCallback, $attributes);
        if ($pattern) {
            $attributeCodes = array_intersect($attributeCodes, array_keys($pattern));
        } elseif (count($rows)) {
            $attributeCodes = array_intersect($attributeCodes, array_keys($rows[0]));
        }

        if (!count($attributeCodes)) {
            return $rows;
        }

        /**
         * @param array $row
         * @return array
         */
        $mapCallback = function (array $row) use ($attributeCodes, $pattern) {
            if ($pattern) {
                foreach ($pattern as $key => $value) {
                    if (in_array($key, $attributeCodes)) {
                        $row[$key] = null;
                    } else {
                        if ($this->isScoped() && $key == $this->getScopeIdentifier()) {
                            continue;
                        }
                        $row[$key] = $value;
                    }
                }
            }

            foreach ($attributeCodes as $attributeCode) {
                if (array_key_exists($attributeCode, $row) && $row[$attributeCode] !== null) {
                    $row[$attributeCode] = null;
                }
            }

            return $row;
        };

        return array_map($mapCallback, $rows);
    }
}
