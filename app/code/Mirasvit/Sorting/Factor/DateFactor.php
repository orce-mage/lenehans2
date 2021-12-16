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
use Mirasvit\Core\Service\CompatibilityService;

class DateFactor implements FactorInterface
{
    use ScoreTrait;

    const DATE_FIELD = 'date_field';
    const ZERO_POINT = 'zero_point';
    const ATTRIBUTE_NEW_FROM = "news_from_date";

    private $context;

    private $indexer;

    public function __construct(
        Context $context,
        FactorIndexer $indexer
    ) {
        $this->context = $context;
        $this->indexer = $indexer;
    }

    public function getName(): string
    {
        return 'Date';
    }

    public function getDescription(): string
    {
        return 'Rank products based on Created At/Updated At/New From date.';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_date';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $dateField = (string)$rankingFactor->getConfigData(self::DATE_FIELD, 'created_at');
        $zeroPoint = (int)$rankingFactor->getConfigData(self::ZERO_POINT, 60);

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();

        if ($dateField == self::ATTRIBUTE_NEW_FROM) {
            $attributeNewFromId = $this->context->eavConfig->getAttribute('catalog_product', self::ATTRIBUTE_NEW_FROM)->getId();
            if (CompatibilityService::isEnterprise()) {
                $select->from(
                    ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                    ['store_id', 'value']
                )->joinInner(
                    ['e' => $resource->getTableName('catalog_product_entity')],
                    'e.row_id = attr_datetime.row_id',
                    ['entity_id']
                )->where(
                    'attr_datetime.attribute_id = ' . $attributeNewFromId
                    . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
                );
            } else {
                $select->from(
                    ['attr_datetime' => $resource->getTableName('catalog_product_entity_datetime')],
                    ['entity_id', 'store_id', 'value']
                )->where(
                    'attr_datetime.attribute_id = ' . $attributeNewFromId
                    . ' AND CURRENT_TIMESTAMP >= attr_datetime.value'
                );
            }
        } else {
            $select->from(
                ['e' => $resource->getTableName('catalog_product_entity')],
                ['entity_id', $dateField]
            );
        }

        if ($productIds) {
            $select->where('entity_id IN (?)', $productIds);
        }

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt, $dateField, $zeroPoint) {
            while ($row = $stmt->fetch()) {
                if ($dateField == 'news_from_date') {
                    $createdAt = $row['value'];
                } else {
                    $createdAt = $row[$dateField];
                }

                $days      = $this->getDaysDiff($createdAt);

                $score = $zeroPoint > $days
                    ? $this->normalize($zeroPoint - $days, 0, $zeroPoint)
                    : 0;

                $value = $createdAt . ' (Days diff: ' . floor($days) . '; Zero point: ' . $zeroPoint . ' days)';

                $this->indexer->add((int)$row['entity_id'], $score, $value);
            }
        });
    }

    private function getDaysDiff(string $date): float
    {
        return (time() - strtotime($date)) / 60 / 60 / 24;
    }
}
