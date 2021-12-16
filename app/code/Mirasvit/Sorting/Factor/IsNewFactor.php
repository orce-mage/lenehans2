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

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class IsNewFactor implements FactorInterface
{
    const ATTRIBUTE_NEW_FROM = "news_from_date";
    const ATTRIBUTE_NEW_TO   = "news_to_date";

    private $context;

    private $indexer;

    /**
     * @var int
     */
    private $attributeNewFromId;

    /**
     * @var int
     */
    private $attributeNewToId;

    private $eavAttribute;

    /**
     * @var bool
     */
    private $isEE = false;

    public function __construct(
        Context $context,
        FactorIndexer $indexer,
        Attribute $eavAttribute
    ) {
        $this->context      = $context;
        $this->indexer      = $indexer;
        $this->eavAttribute = $eavAttribute;
    }

    public function getName(): string
    {
        return 'New Product';
    }

    public function getDescription(): string
    {
        return 'Rank products based on product new from - to values.';
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        if ($productIds) {
            return;
        }

        if (!$this->getAttributeIdByCode(self::ATTRIBUTE_NEW_FROM)
            || !$this->getAttributeIdByCode(self::ATTRIBUTE_NEW_FROM)) {
            return;
        }

        $this->attributeNewFromId = $this->getAttributeIdByCode(self::ATTRIBUTE_NEW_FROM);
        $this->attributeNewToId   = $this->getAttributeIdByCode(self::ATTRIBUTE_NEW_TO);

        $this->isEE = CompatibilityService::isEnterprise();

        $validFromProductIds = $this->getValidFrom();
        $validToProductIds   = $this->getValidTo();

        $validProductIds = [];

        foreach ($validFromProductIds as $id) {
            if (!in_array($id, $validToProductIds)) {
                $validProductIds[] = $id;
            }
        }

        $this->indexer->process($rankingFactor, $productIds, function () use ($validFromProductIds) {
            foreach ($validFromProductIds as $product) {
                $value = 'Product set as new from ' . $product['value'];
                $this->indexer->add((int)$product['entity_id'], IndexInterface::MAX, $value);
            }
        });
    }

    private function getValidFrom(): array
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $selectValidFrom = $connection->select();

        if ($this->isEE) {
            $selectValidFrom->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['store_id', 'value']
            )->joinInner(
                ['e' => $resource->getTableName('catalog_product_entity')],
                'e.row_id = attr_datetime.row_id',
                ['entity_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewFromId
                . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
            );
        } else {
            $selectValidFrom->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['entity_id', 'store_id', 'value']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewFromId
                . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
            );
        }

        $validFrom = $connection->query($selectValidFrom)->fetchAll();

        if (!count($validFrom)) {
            return [];
        }

        $idsFrom = array_map(function ($row) {
            return $row['entity_id'];
        }, $validFrom);

        //need this to remove ids with invalid value news_to_date
        if ($this->isEE) {
            $correctionSelect = $connection->select()->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['store_id']
            )->joinInner(
                ['e' => $resource->getTableName('catalog_product_entity')],
                'e.row_id = attr_datetime.row_id',
                ['entity_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewToId
                . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
                . ' AND e.entity_id IN (' . implode(',', $idsFrom) . ')'
            );
        } else {
            $correctionSelect = $connection->select()->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['entity_id', 'store_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewToId
                . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
                . ' AND attr_datetime.entity_id IN (' . implode(',', $idsFrom) . ')'
            );
        }

        $correction = $connection->query($correctionSelect)->fetchAll();

        $validFrom = array_filter($validFrom, function ($row) use ($correction) {
            return !in_array($row, $correction);
        });

        return $validFrom;
    }

    private function getValidTo(): array
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $selectValidTo = $connection->select();

        if ($this->isEE) {
            $selectValidTo->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['store_id', 'value']
            )->joinInner(
                ['e' => $resource->getTableName('catalog_product_entity')],
                'e.row_id = attr_datetime.row_id',
                ['entity_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewToId
                . ' AND CURRENT_TIMESTAMP <= attr_datetime.value'
            );
        } else {
            $selectValidTo->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['entity_id', 'store_id', 'value']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewToId
                . ' AND CURRENT_TIMESTAMP <= attr_datetime.value'
            );
        }

        $validTo = $connection->query($selectValidTo)->fetchAll();

        if (!count($validTo)) {
            return [];
        }

        $idsTo = array_map(function ($row) {
            return $row['entity_id'];
        }, $validTo);

        //need this to remove ids with invalid value news_from_date
        if ($this->isEE) {
            $correctionSelect = $connection->select()->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['store_id']
            )->joinInner(
                ['e' => $resource->getTableName('catalog_product_entity')],
                'e.row_id = attr_datetime.row_id',
                ['entity_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewFromId
                . ' AND CURRENT_TIMESTAMP <= attr_datetime.value'
                . ' AND e.entity_id IN (' . implode(',', $idsTo) . ')'
            );
        } else {
            $correctionSelect = $connection->select()->from(
                ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                ['entity_id', 'store_id']
            )->where(
                'attr_datetime.attribute_id = ' . $this->attributeNewFromId
                . ' AND CURRENT_TIMESTAMP <= attr_datetime.value'
                . ' AND attr_datetime.entity_id IN (' . implode(',', $idsTo) . ')'
            );
        }

        $correction = $connection->query($correctionSelect)->fetchAll();

        $validTo = array_filter($validTo, function ($row) use ($correction) {
            return !in_array($row, $correction);
        });

        return $validTo;
    }

    private function getAttributeIdByCode(string $code): ?int
    {
        return (int)$this->eavAttribute->getIdByCode('catalog_product', $code) ? : null;
    }
}
