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
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class ImageFactor implements FactorInterface
{
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
        return 'Image';
    }

    public function getDescription(): string
    {
        return 'Rank products based on image availability.';
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id']
        )->group('entity_id');

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $attribute = $this->context->eavConfig->getAttribute('catalog_product', 'image');

        $conditions = [
            'eav.attribute_id = ' . (int)$attribute->getId(),
            CompatibilityService::isEnterprise() ? 'eav.row_id = e.row_id' : 'eav.entity_id = e.entity_id',
        ];

        $select->joinLeft(
            ['eav' => $attribute->getBackend()->getTable()],
            implode(' AND ', $conditions),
            ['value']
        );

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt) {
            while ($row = $stmt->fetch()) {
                $value = $row['value'];

                $score = IndexInterface::MAX;

                if (!$value || $value == 'no_selection') {
                    $score = IndexInterface::MIN;
                }

                $this->indexer->add((int)$row['entity_id'], $score, $value);
            }
        });
    }
}
