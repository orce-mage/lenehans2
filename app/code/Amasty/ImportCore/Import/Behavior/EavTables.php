<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterfaceFactory;
use Amasty\ImportCore\Import\Utils\Hash;
use Amasty\ImportCore\Import\Utils\MetadataSearcher;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class EavTables
{
    /**
     * @var array
     */
    protected $nonEavFieldNames = [
        'entity_id',
        'store_id',
        'row_id'
    ];

    /**
     * @var array
     */
    private $config;

    /**
     * @var BehaviorResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Hash
     */
    protected $hash;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var int[]
     */
    private $scopeCodes;

    /**
     * @var MetadataSearcher
     */
    private $metadataSearcher;

    /**
     * @var string
     */
    protected $eavEntityType;

    /**
     * @var string
     */
    protected $entityTable;

    /**
     * @var string
     */
    protected $linkField;

    public function __construct(
        BehaviorResultInterfaceFactory $behaviorResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        ResourceConnection $resourceConnection,
        Hash $hash,
        StoreManagerInterface $storeManager,
        MetadataSearcher $metadataSearcher,
        array $config
    ) {
        $this->eavEntityType = $config['eavEntityType'];
        $this->config = $config;
        $this->resultFactory = $behaviorResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->resourceConnection = $resourceConnection;
        $this->hash = $hash;
        $this->storeManager = $storeManager;
        $this->metadataSearcher = $metadataSearcher;
        $this->setEntityTable();
        $this->setLinkField();

        if (isset($this->config['scopeType'])
            && !isset($this->config['scopeIdentifier'])
        ) {
            throw new \RuntimeException('scopeIdentifier isn\'t specified.');
        }
    }

    /**
     * Get eav attributes
     *
     * @param array $includedAttrCodes
     * @param int|null $attributeSetId
     * @return AttributeInterface[]
     */
    protected function getEavAttributes(array $includedAttrCodes, ?int $attributeSetId = null): array
    {
        $hash = $this->hash->hash(json_encode($includedAttrCodes));
        if (isset($this->attributes[$hash])) {
            return $this->attributes[$hash];
        }

        if ($attributeSetId === null) {
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', null, 'neq');
        } else {
            $this->searchCriteriaBuilder->addFilter('attribute_set_id', $attributeSetId);
        }

        $criteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeInterface::ATTRIBUTE_CODE, $includedAttrCodes, 'in')
            ->addFilter(AttributeInterface::ATTRIBUTE_CODE, $this->nonEavFieldNames, 'nin')
            ->create();

        $attributes = $this->attributeRepository->getList($this->eavEntityType, $criteria)->getItems();

        return $this->attributes[$hash] = $attributes;
    }

    /**
     * Key attributes by attribute code
     *
     * @param AttributeInterface[] $attributes
     * @return AttributeInterface[]
     */
    protected function keyByAttributeCode(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute;
        }

        return $result;
    }

    /**
     * Retrieves scope value from specified data row
     *
     * @param array $row
     * @return int|null
     */
    protected function getScopeValue(array $row): ?int
    {
        if (isset($this->config['scopeType'])) {
            $scopeIdentifier = $this->config['scopeIdentifier'];
            if ($scopeIdentifier && isset($row[$scopeIdentifier])) {
                return (int)$row[$scopeIdentifier];
            }
        }

        return null;
    }

    protected function getEavTableName(string $backendType): string
    {
        switch ($backendType) {
            case 'varchar':
                $eavTableName = $this->entityTable . '_varchar';
                break;
            case 'text':
                $eavTableName = $this->entityTable . '_text';
                break;
            case 'int':
            case 'integer':
                $eavTableName = $this->entityTable . '_int';
                break;
            case 'decimal':
                $eavTableName = $this->entityTable . '_decimal';
                break;
            case 'datetime':
                $eavTableName = $this->entityTable . '_datetime';
                break;
            default:
                $eavTableName = '';
                break;
        }

        return $eavTableName;
    }

    /**
     * Prepare attribute values
     *
     * @param array $attrValues
     * @return array
     */
    protected function prepareAttributeValues(array $attrValues): array
    {
        if (isset($this->config['scopeType'])) {
            $scopeCodes = $this->getScopeCodes($this->config['scopeType']);
            $scopeIdentifier = $this->config['scopeIdentifier'];

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
                return $filteredAttrValues;
            }
            if (count($attrValues) == 1) {
                $attrValues[0][$scopeIdentifier] = 0;
            }
        }

        return $attrValues;
    }

    /**
     * Retrieve valid scope codes
     *
     * @param string $scopeType
     * @return int[]
     */
    private function getScopeCodes(string $scopeType): array
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

    private function setLinkField(): void
    {
        $metadata = $this->metadataSearcher->searchMetadata($this->eavEntityType);
        if ($metadata) {
            $this->linkField = $metadata->getLinkField();
        } elseif ($this->entityTable) {
            $this->linkField = $this->resourceConnection->getConnection()
                ->getAutoIncrementField($this->entityTable);
        } else {
            throw new \RuntimeException(__('No entity metadata or entity table found.')->render());
        }
    }

    private function setEntityTable(): void
    {
        $this->entityTable = $this->resourceConnection->getTableName($this->config['entityTable']);
    }
}
