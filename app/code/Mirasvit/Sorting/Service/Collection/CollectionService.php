<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Service\Collection;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Sorting\Api\Data\IndexInterface;

class CollectionService
{
    private $resource;

    private $attributeRepository;

    private $tableMaintainer;

    private $storeManager;

    private $registry;

    public function __construct(
        ResourceConnection $resource,
        ProductAttributeRepositoryInterface $attributeRepository,
        TableMaintainer $tableMaintainer,
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->resource            = $resource;
        $this->attributeRepository = $attributeRepository;
        $this->tableMaintainer     = $tableMaintainer;
        $this->storeManager        = $storeManager;
        $this->registry            = $registry;
    }

    public function joinSortingIndex(Select $select): void
    {
        $tableName = $this->resource->getTableName(IndexInterface::TABLE_NAME);
        $storeId   = (int)$this->storeManager->getStore()->getId();

        $this->joinTable(
            $select,
            IndexInterface::TABLE_NAME,
            $tableName,
            [
                IndexInterface::TABLE_NAME . '.product_id = e.entity_id',
                IndexInterface::TABLE_NAME . ".store_id = $storeId"
            ]
        );

        $this->joinTable(
            $select,
            IndexInterface::TABLE_NAME.'_0',
            $tableName,
            [
                IndexInterface::TABLE_NAME.'_0' . '.product_id = e.entity_id',
                IndexInterface::TABLE_NAME.'_0' . ".store_id = 0"
            ]
        );
    }

    public function joinAttribute(Select $select, string $attributeCode): ?string
    {
        $storeId    = (int)$this->storeManager->getStore()->getId();
        $websiteId  = (int)$this->storeManager->getWebsite()->getId();
        $tableAlias = 'sorting_' . $attributeCode;

        if ($attributeCode == 'position') {
            if (!$this->registry->registry('current_category')) {
                return null;
            }

            $this->joinTable(
                $select,
                $tableAlias,
                $this->tableMaintainer->getMainTable($storeId),
                [
                    "{$tableAlias}.product_id = e.entity_id",
                    "{$tableAlias}.store_id = {$storeId}",
                    "{$tableAlias}.category_id = " . (int)$this->registry->registry('current_category')->getId(),
                ]
            );

            return $tableAlias . '.position';
        } elseif ($attributeCode == 'price') {

            $this->joinTable(
                $select,
                $tableAlias,
                $this->resource->getTableName('catalog_product_index_price'),
                [
                    "{$tableAlias}.entity_id = e.entity_id",
                    "{$tableAlias}.website_id = {$websiteId}",
                    "{$tableAlias}.customer_group_id = 0",
                ]
            );

            return $tableAlias . '.min_price';
        }

        try {
            $attribute = $this->attributeRepository->get($attributeCode);
        } catch (\Exception$e) {
            return null;
        }

        if ($attribute->getBackend()->isStatic()) {
            return 'e.' . $attributeCode;
        }

        $this->joinTable(
            $select,
            $tableAlias.'_store',
            $attribute->getBackend()->getTable(),
            [
                CompatibilityService::isEnterprise() ? "e.row_id = {$tableAlias}_store.row_id" : "e.entity_id = {$tableAlias}_store.entity_id",
                "{$tableAlias}_store.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_store.store_id = {$storeId}",
            ]
        );

        $this->joinTable(
            $select,
            $tableAlias.'_global',
            $attribute->getBackend()->getTable(),
            [
                CompatibilityService::isEnterprise() ? "e.row_id = {$tableAlias}_global.row_id" : "e.entity_id = {$tableAlias}_global.entity_id",
                "{$tableAlias}_global.attribute_id = " . (int)$attribute->getId(),
                "{$tableAlias}_global.store_id = 0",
            ]
        );

        return 'IFNULL('.$tableAlias . '_store.value, '.$tableAlias.'_global.value)';
    }

    public function addOrder(Select $select, array $expressions, ?string $direction): void
    {
        $expressions = array_filter($expressions);

        if (!count($expressions)) {
            return;
        }

        foreach ($expressions as $key => $expr) {
            if (is_array($expr)) {
                $expressions[$key] = implode(' ', $expr);
            }
        }

        $expressions = implode(' + ', $expressions);

        $select->order(new \Zend_Db_Expr($expressions . ' ' . $direction));
    }

    private function joinTable(Select $select, string $alias, string $name, array $conditions): void
    {
        foreach ($select->getPart(\Zend_Db_Select::FROM) as $aliasName => $item) {
            if ($item['tableName'] === $name && $aliasName === $alias) {
                return;
            }
        }

        $select->joinLeft([$alias => $name], implode(' AND ', $conditions), []);
    }
}
