<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterfaceFactory;
use Magento\Eav\Model\ResourceModel\AttributeLoader;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributePool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class EavEntityManager
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var BehaviorResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var AttributePool
     */
    protected $attributePool;

    /**
     * @var AttributeLoader
     */
    protected $attributeLoader;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var int[]
     */
    private $scopeCodes;

    public function __construct(
        BehaviorResultInterfaceFactory $behaviorResultFactory,
        AttributePool $attributePool,
        AttributeLoader $attributeLoader,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        array $config
    ) {
        $this->config = $config;
        $this->resultFactory = $behaviorResultFactory;
        $this->attributePool = $attributePool;
        $this->attributeLoader = $attributeLoader;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;

        if (!isset($this->config['entityDataInterface'])) {
            throw new \RuntimeException('entityDataInterface isn\'t specified.');
        }
        $this->entityType = $this->config['entityDataInterface'];

        if (isset($this->config['scopeType'])
            && !isset($this->config['scopeIdentifier'])
        ) {
            throw new \RuntimeException('scopeIdentifier isn\'t specified.');
        }
    }

    /**
     * Prepare attribute values
     *
     * @param array $attrValues
     * @return array
     */
    protected function prepareAttributeValues(array $attrValues): array
    {
        if ($this->isScoped()) {
            $scopeCodes = $this->getScopeCodes($this->getScopeType());
            $scopeIdentifier = $this->getScopeIdentifier();

            /**
             * @param array $row
             * @return bool
             */
            $filerRowsCallback = function (array $row) use ($scopeIdentifier, $scopeCodes) {
                $scopeValue = $row[$scopeIdentifier] ?? null;

                return $scopeValue !== null
                    && in_array($scopeValue, $scopeCodes);
            };

            $filteredAttrValues = array_filter($attrValues, $filerRowsCallback);
            if (count($filteredAttrValues) || count($attrValues) > 1) {
                return $this->mapLinkField($filteredAttrValues);
            }
            if (count($attrValues) == 1) {
                $attrValues[0][$scopeIdentifier] = 0;
            }
        }

        return $this->mapLinkField($attrValues);
    }

    /**
     * Adds a column with mapped link field value to attribute values rows
     *
     * @param array $attrValues
     * @return array
     * @throws \Exception
     */
    private function mapLinkField(array $attrValues): array
    {
        if (!count($attrValues)) {
            return $attrValues;
        }

        $metadata = $this->metadataPool->getMetadata($this->entityType);
        $identifierField = $metadata->getIdentifierField();
        $linkField = $metadata->getLinkField();
        if (!isset($attrValues[0][$identifierField]) || $identifierField == $linkField) {
            return $attrValues;
        }

        $ids = array_column($attrValues, $identifierField);
        $ids = array_unique($ids);
        $mapping = $this->getIdToLinkFieldMapping($ids, $metadata);

        /**
         * @param array $row
         * @return array
         */
        $mapCallback = function (array $row) use ($mapping, $identifierField, $linkField) {
            $id = $row[$identifierField] ?? null;
            if ($id && isset($mapping[$id]) && !isset($row[$linkField])) {
                $row[$linkField] = $mapping[$id];
            }

            return $row;
        };

        return array_map($mapCallback, $attrValues);
    }

    /**
     * Get entity id to link fild mapping
     *
     * @param array $ids
     * @param EntityMetadataInterface $metadata
     * @return array
     */
    private function getIdToLinkFieldMapping(array $ids, EntityMetadataInterface $metadata): array
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $metadata->getEntityConnectionName()
        );
        $identifierField = $metadata->getIdentifierField();
        $select = $connection->select()
            ->from(
                $metadata->getEntityTable(),
                [
                    $identifierField,
                    $metadata->getLinkField()
                ]
            )->where(
                $identifierField . ' IN (?)',
                $ids
            );

        return $connection->fetchPairs($select);
    }

    /**
     * Retrieve valid scope codes
     *
     * @param string $scopeType
     * @return int[]
     */
    protected function getScopeCodes($scopeType)
    {
        if (!$this->scopeCodes) {
            $stores = $this->storeManager->getStores(true);
            foreach ($stores as $store) {
                $this->scopeCodes[] = $scopeType == ScopeInterface::SCOPE_WEBSITE
                    ? $store->getWebsiteId()
                    : $store->getId();
            }
        }

        return $this->scopeCodes;
    }

    /**
     * Apply attribute handler actions
     *
     * @param AttributeInterface[] $actions
     * @param array $row
     */
    protected function applyActions(array $actions, array $row)
    {
        foreach ($actions as $action) {
            $action->execute($this->entityType, $row);
        }
    }

    /**
     * Checks if behavior works with values by scope
     *
     * @return bool
     */
    protected function isScoped()
    {
        return isset($this->config['scopeType']);
    }

    /**
     * Get scope type
     *
     * @return string|null
     */
    protected function getScopeType()
    {
        return $this->config['scopeType'] ?? null;
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
}
