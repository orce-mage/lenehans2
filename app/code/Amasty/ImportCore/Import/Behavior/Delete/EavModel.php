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
use Amasty\ImportCore\Import\Behavior\EavModel as EavModelBehavior;

class EavModel extends EavModelBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();

        $idFieldName = $this->getIdFieldName();
        $data = $this->prepareAttributeValues($data);
        if (!count($data) || !isset($data[0][$idFieldName])) {
            return $result;
        }

        $id = (int)$data[0][$idFieldName];
        foreach ($this->getAttributesForDeleteByScope($data) as $attrData) {
            $model = $this->loadForScope($id, $attrData['scope']);
            if ($model) {
                $model->setData(
                    $this->getNullValuesData($model->getData(), $attrData['attributeCodes'])
                );
                $this->save($model);
            }
        }

        return $result;
    }

    /**
     * Get attribute codes for delete by scopes
     *
     * @param array $data
     * @return array
     */
    private function getAttributesForDeleteByScope(array $data): array
    {
        $result = [];

        $attributeCodes = $this->getAttributeCodes();
        $attributeCodes = array_diff($attributeCodes, $this->nonEavFieldNames);

        $scopeIdentifier = $this->getScopeIdentifier();
        if ($scopeIdentifier
            && count($data) == 1
            && $data[0][$scopeIdentifier] == 0
        ) {
            $patternKeys = array_keys($data[0]);
            foreach ($this->getScopeCodes() as $scopeCode) {
                $result[] = [
                    'scope' => $scopeCode,
                    'attributeCodes' => array_intersect($attributeCodes, $patternKeys)
                ];
            }
        } else {
            foreach ($data as $row) {
                $rowKeys = array_keys($row);
                $result[] = [
                    'scope' => $scopeIdentifier
                        ? (int) $row[$scopeIdentifier]
                        : null,
                    'attributeCodes' => array_intersect($attributeCodes, $rowKeys)
                ];
            }
        }

        return $result;
    }

    /**
     * Get data with null values
     *
     * @param array $entityData
     * @param array $keys
     * @return array
     */
    private function getNullValuesData(array $entityData, array $keys): array
    {
        foreach ($keys as $key) {
            $entityData[$key] = null;
        }

        return $entityData;
    }
}
