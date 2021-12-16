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

class AttributeSetFactor implements FactorInterface
{
    use MappingTrait;

    const MAPPING = 'mapping';

    private $indexer;

    public function __construct(
        FactorIndexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    public function getName(): string
    {
        return 'Attribute Set';
    }

    public function getDescription(): string
    {
        return 'Rank products based on attribute set.';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_attributeSet';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $mapping = $rankingFactor->getConfigData(self::MAPPING, []);

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id', 'attribute_set_id']
        );

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt, $mapping) {
            while ($row = $stmt->fetch()) {
                $value = $row['attribute_set_id'];
                $score = $this->getValue($mapping, $value);
                $value = $this->formatValue($mapping, $value);

                $this->indexer->add((int)$row['entity_id'], $score, $value);
            }
        });
    }
}
