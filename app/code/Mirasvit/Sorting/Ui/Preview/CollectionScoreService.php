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

namespace Mirasvit\Sorting\Ui\Preview;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Model\Indexer;

class CollectionScoreService
{
    private $resource;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    public function sortCollection(
        AbstractCollection $collection,
        array $globalFactors,
        CriterionInterface $criterion
    ): AbstractCollection {

        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::ORDER);

        $select->joinLeft(
            ['sorting_index' => $this->resource->getTableName(IndexInterface::TABLE_NAME)],
            'sorting_index.product_id = e.entity_id and sorting_index.store_id = 0',
            []
        );

        $globalFormula = [];
        foreach ($globalFactors as $factor) {
            $columnName      = Indexer::getScoreColumn($factor);
            $globalFormula[] = 'IFNULL(sorting_index.' . $columnName . ', 0) * ' . $factor->getWeight();
        }

        if ($globalFormula) {
            $globalExpr = new \Zend_Db_Expr(implode(' + ', $globalFormula));

            $select->columns(['sorting_score_global' => $globalExpr])
                ->order(new \Zend_Db_Expr($globalExpr . ' desc'));
        }


        foreach ($criterion->getConditionCluster()->getFrames() as $frameIdx => $frame) {
            $localFormula = [];

            $dir = $frameIdx === 0 ? $frame->getDirection() : 'desc';

            foreach ($frame->getNodes() as $node) {
                if ($node->getSortBy() == CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE) {
                    $attribute = $node->getAttribute();
                    $collection->addAttributeToSort($attribute, $dir);
                } else {
                    $columnName     = Indexer::getScoreColumnById($node->getRankingFactor());
                    $localFormula[] = 'IFNULL(sorting_index.' . $columnName . ', 0) * ' . $node->getWeight();
                }
            }

            if (count($localFormula)) {
                $localExpr = new \Zend_Db_Expr(implode(' + ', $localFormula));

                $select->order(new \Zend_Db_Expr($localExpr . ' ' . $dir))
                    ->columns(['sorting_score_' . $frameIdx => $localExpr]);
            }
        }

        return $collection;
    }
}
