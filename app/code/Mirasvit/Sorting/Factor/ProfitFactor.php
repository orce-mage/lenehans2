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

class ProfitFactor implements FactorInterface
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
        return 'Profit';
    }

    public function getDescription(): string
    {
        return "Calculation: The products' price and cost determines the products' ranking of this factor.";
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
        );

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $costAttribute  = $this->context->eavConfig->getAttribute('catalog_product', 'cost');
        $priceAttribute = $this->context->eavConfig->getAttribute('catalog_product', 'price');

        $select->joinLeft(
            ['cost' => $costAttribute->getBackend()->getTable()],
            implode(' AND ', [
                'cost.attribute_id = ' . $costAttribute->getId(),
                CompatibilityService::isEnterprise()
                    ? 'cost.row_id = e.row_id'
                    : 'cost.entity_id = e.entity_id',
            ]),
            ['cost' => 'value']
        );

        $select->joinLeft(
            ['price' => $priceAttribute->getBackend()->getTable()],
            implode(' AND ', [
                'price.attribute_id = ' . $priceAttribute->getId(),
                CompatibilityService::isEnterprise()
                    ? 'price.row_id = e.row_id'
                    : 'price.entity_id = e.entity_id',
            ]),
            ['price' => 'value']
        );

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt) {
            while ($row = $stmt->fetch()) {
                $cost  = $row['cost'];
                $price = $row['price'];

                if (!$cost || !$price) {
                    $score = 0;
                } elseif ($cost > $price) {
                    $score = IndexInterface::MIN;
                } else {
                    $score = (1 - $cost / $price) * IndexInterface::MAX;
                }

                $this->indexer->add((int)$row['entity_id'], $score, "price=$price (cost=$cost)");
            }
        });
    }
}
