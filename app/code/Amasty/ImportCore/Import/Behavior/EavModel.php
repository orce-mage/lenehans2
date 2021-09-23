<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterfaceFactory;
use Amasty\ImportCore\Import\Utils\DuplicateFieldChecker;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class EavModel extends Model
{
    /**
     * @var array
     */
    protected $nonEavFieldNames = [
        'entity_id',
        'store_id',
        'attribute_set_id',
        'row_id'
    ];

    /**
     * @var int[]
     */
    private $scopeCodes;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        BehaviorResultInterfaceFactory $behaviorResultFactory,
        StoreManagerInterface $storeManager,
        DuplicateFieldChecker $duplicateFieldChecker,
        array $config
    ) {
        parent::__construct(
            $objectManager,
            $behaviorResultFactory,
            $duplicateFieldChecker,
            $config
        );
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare attribute values
     *
     * @param array $data
     * @return array
     */
    protected function prepareAttributeValues(array $data): array
    {
        $scopeIdentifier = $this->getScopeIdentifier();
        if (!$scopeIdentifier) {
            $attrValues = [$data[0]] ?? [];
        } else {
            $attrValues = $data;
        }

        if (empty($attrValues)) {
            return [];
        }

        $idFieldName = $this->getIdFieldName();
        $colsToRemove = $this->getRedundantAttrDataFields($attrValues[0]);
        $colsToRemoveFlipped = array_flip($colsToRemove);

        /**
         * @param array $row
         * @return bool
         */
        $filterRowsCallback = function (array $row) use ($idFieldName, $scopeIdentifier) {
            if ($scopeIdentifier && !isset($row[$scopeIdentifier])) {
                return false;
            }
            if (!isset($row[$idFieldName])) {
                return false;
            }

            return true;
        };

        /**
         * @param array $row
         * @return array
         */
        $filerColsCallback = function (array $row) use ($colsToRemoveFlipped) {
            return array_diff_key($row, $colsToRemoveFlipped);
        };

        $attrValues = array_filter($attrValues, $filterRowsCallback);

        return array_map($filerColsCallback, $attrValues);
    }

    /**
     * Returns attribute values data keys that aren't attribute codes for current entity type
     *
     * @param array $row
     * @return array
     */
    private function getRedundantAttrDataFields(array $row)
    {
        $redundantFields = [];
        $attributeCodes = $this->getAttributeCodes();

        foreach (array_keys($row) as $field) {
            if (!in_array($field, $attributeCodes) && !in_array($field, $this->nonEavFieldNames)) {
                $redundantFields[] = $field;
            }
        }

        return array_unique($redundantFields);
    }

    /**
     * Get entity attribute codes
     *
     * @return array
     */
    protected function getAttributeCodes()
    {
        /** @var AbstractModel $modelProto */
        $modelProto = $this->modelFactory->create();
        /** @var AbstractEntity $resourceProto */
        $resourceProto = $modelProto->getResource();
        if (!$resourceProto instanceof AbstractEntity) {
            throw new \LogicException($this->config['modelFactory'] . ' is not a factory of EAV entity');
        }

        $resourceProto->loadAllAttributes($modelProto);
        $attributesByCode = $resourceProto->getAttributesByCode();

        return array_keys($attributesByCode);
    }

    /**
     * Retrieves scope value from specified data row
     *
     * @param array $row
     * @return int|null
     */
    protected function getScopeValue(array $row)
    {
        $scopeIdentifier = $this->getScopeIdentifier();
        if ($scopeIdentifier) {
            return (int)$row[$scopeIdentifier] ?? null;
        }

        return null;
    }

    /**
     * Get scope identifier field
     *
     * @return string|null
     */
    protected function getScopeIdentifier()
    {
        return $this->config['scopeIdentifier'] ?? null;
    }

    /**
     * Get scope type
     *
     * @return string|null
     */
    protected function getScopeType()
    {
        if (!$this->getScopeIdentifier()) {
            return null;
        }

        return $this->config['scopeType'] ?? ScopeInterface::SCOPE_STORE;
    }

    /**
     * Retrieve valid scope codes
     *
     * @return int[]
     */
    protected function getScopeCodes()
    {
        if (!$this->scopeCodes) {
            $stores = $this->storeManager->getStores(true);
            foreach ($stores as $store) {
                $this->scopeCodes[] = $this->getScopeType() == ScopeInterface::SCOPE_WEBSITE
                    ? $store->getWebsiteId()
                    : $store->getId();
            }
        }

        return $this->scopeCodes;
    }

    /**
     * @inheritDoc
     */
    protected function initLoad()
    {
        $this->loadCallback = function (int $id, ?int $scopeValue = null) {
            /** @var AbstractModel $model */
            $model = $this->modelFactory->create();
            $scopeIdentifier = $this->getScopeIdentifier();
            if ($scopeIdentifier && $scopeValue !== null) {
                // This approach of loading is incorrect
                // because for non default scope may be loaded data from default one
                $model->setData($scopeIdentifier, $scopeValue);
            }

            return $model->load($id);
        };
    }

    public function loadForScope(int $id, ?int $scopeValue = null)
    {
        if (!$this->loadCallback) {
            $this->initLoad();
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $model = call_user_func_array($this->loadCallback, [$id, $scopeValue]);
        if (!$model->getId()) {
            return null;
        }

        return $model;
    }
}
