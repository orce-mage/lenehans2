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

class RatingFactor implements FactorInterface
{
    use ScoreTrait;

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
        return 'Product Rating';
    }

    public function getDescription(): string
    {
        return 'Rank products based on overall rating.';
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
        )->joinInner(
            ['vote' => $resource->getTableName('rating_option_vote')],
            'vote.entity_pk_value = e.entity_id',
            ['value' => new \Zend_Db_Expr('AVG(vote.percent) - 1 + COUNT(vote.vote_id) / 1000')]
        )->joinInner(
            ['review' => $resource->getTableName('review')],
            'review.review_id = vote.review_id',
            []
        )->where('review.status_id=1')
            ->group('e.entity_id');

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $stmt = $connection->query($select);

        $this->indexer->process($rankingFactor, $productIds, function () use ($stmt) {
            while ($row = $stmt->fetch()) {
                $value = $row['value'];

                $score = $this->normalize((float)$value, 0, 100);

                $this->indexer->add((int)$row['entity_id'], $score, $value);
            }
        });

    }
}
