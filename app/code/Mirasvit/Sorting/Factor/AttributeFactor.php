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

use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class AttributeFactor implements FactorInterface
{
    use MappingTrait;

    const ATTRIBUTE = 'attribute';
    const MAPPING   = 'mapping';

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
        return 'Attribute';
    }

    public function getDescription(): string
    {
        return "Rank products based on attribute' values.";
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_attribute';
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $attribute = $rankingFactor->getConfigData(self::ATTRIBUTE);
        $mapping   = $rankingFactor->getConfigData(self::MAPPING, []);

        if (!$attribute) {
            return;
        }

        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id']
        );

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $attrModel = $this->context->eavConfig->getAttribute('catalog_product', $attribute);

        if ($attrModel->getBackend()->getTable() == 'catalog_product_entity') {
            return;
        }

        $select->joinLeft(
            ['eav' => $attrModel->getBackend()->getTable()],
            implode(' AND ', [
                'eav.attribute_id = ' . $attrModel->getId(),
                CompatibilityService::isEnterprise()
                    ? 'eav.row_id = e.row_id'
                    : 'eav.entity_id = e.entity_id',
            ]),
            ['value']
        );

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt, $mapping) {
            while ($row = $stmt->fetch()) {
                $value = (string)$row['value'];

                $score = $this->getValue($mapping, $value);

                $value = $this->formatValue($mapping, $value);

                $this->indexer->add((int)$row['entity_id'], $score, $value);
            }
        });
    }
}
