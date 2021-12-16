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

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;
use Magento\Store\Model\StoreManagerInterface;

class AlphanumericFactor implements FactorInterface
{
    use ScoreTrait;

    const ATTRIBUTE = 'attribute';

    private $context;

    private $indexer;

    private $storeManager;

    public function __construct(
        Context $context,
        FactorIndexer $indexer,
        StoreManagerInterface $storeManager
    ) {
        $this->context      = $context;
        $this->indexer      = $indexer;
        $this->storeManager = $storeManager;
    }

    public function getName(): string
    {
        return 'Alphanumeric';
    }

    public function getDescription(): string
    {
        return 'Rank products based on Alphanumeric (Natural) sort order. I.e. sort as Z1.1, Z2, Z11, instead of Z11, Z1.1, Z2';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_alphanumeric';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $attributeCode = $rankingFactor->getConfigData(self::ATTRIBUTE);
        $attribute = $this->context->eavConfig->getAttribute('catalog_product', $attributeCode);

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $stores = $this->storeManager->getStores();
        $this->indexer->process($rankingFactor, $productIds , function () use ($resource, $connection, $stores, $attribute) {
            foreach ($stores as $store) {
                $select = $connection->select();
                $store_id = (int) $store->getId();

                if ($attribute->getBackendType() == 'static') {
                    $select->from(
                        ['e' => $resource->getTableName('catalog_product_entity')],
                        [new \Zend_Db_Expr('group_concat(e.entity_id) as entity_ids'), $attribute->getAttributeCode() .' as value']
                    );
                } else {
                    if ($attribute->getFrontendInput() == 'texteditor' || $attribute->getFrontendInput() == 'textarea') {
                        $select->from(
                            ['e' => $resource->getTableName('catalog_product_entity')],
                            [new \Zend_Db_Expr('group_concat(e.entity_id) as entity_ids')]
                        )->joinInner(
                            ['ev' => $resource->getTableName('catalog_product_entity_text')],
                            'e.entity_id = ev.entity_id',
                            [new \Zend_Db_Expr('TRIM(REPLACE(value, " ", "")) as value')]
                        )->where('ev.attribute_id =?', $attribute->getId()
                        )->where('ev.store_id IN (?)', [0 , $store_id]);
                    } else {
                        $select->from(
                            ['e' => $resource->getTableName('catalog_product_entity')],
                            [new \Zend_Db_Expr('group_concat(e.entity_id) as entity_ids')]
                        )->joinInner(
                            ['ev' => $resource->getTableName('catalog_product_entity_varchar')],
                            'e.entity_id = ev.entity_id',
                            [new \Zend_Db_Expr('TRIM(REPLACE(value, " ", "" )) as value')]
                        )->where('ev.attribute_id =?', $attribute->getId()
                        )->where('ev.store_id IN (?)', [0 , $store_id]);
                    }
                }

                $select->group('value')
                    ->order(new \Zend_Db_Expr('CAST(value AS UNSIGNED) asc'));

                $stmt = $connection->query($select);

                if ($stmt->rowCount() == 0) {
                    continue;
                }

                $unit = 100 / $stmt->rowCount();

                $score = 1;
                while ($row = $stmt->fetch()) {
                    foreach (explode(',', $row['entity_ids']) as $entity_id) {
                        $this->indexer->add((int) $entity_id, $score * $unit, $row['value'], $store_id);
                    }
                    $score++;
                }
            }
        });
    }
}
