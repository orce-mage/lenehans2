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

use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class RuleFactor implements FactorInterface
{
    const RULE = 'rule';

    /**
     * @var array
     */
    private $productIds = [];

    private $ruleFactory;

    private $productCollectionFactory;

    private $iterator;

    private $productFactory;

    private $context;

    private $indexer;

    public function __construct(
        ProductRule\RuleFactory $ruleFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        Iterator $iterator,
        Context $context,
        FactorIndexer $indexer
    ) {
        $this->ruleFactory              = $ruleFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->iterator                 = $iterator;
        $this->productFactory           = $productFactory;
        $this->context                  = $context;
        $this->indexer                  = $indexer;
    }

    public function getName(): string
    {
        return 'Rule';
    }

    public function getDescription(): string
    {
        return 'Rank products based on various different conditions.';
    }

    public function getUiComponent(): ?string
    {
        return 'sorting_factor_rule';
    }

    public function getRule(RankingFactorInterface $rankingFactor): ProductRule\Rule
    {
        $ruleData = $rankingFactor->getConfigData(self::RULE, []);

        $model = $this->ruleFactory->create();

        $model->loadPost($ruleData);

        return $model;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        $rule = $this->getRule($rankingFactor);

        $productCollection = $this->productCollectionFactory->create();

        if ($productIds) {
            $productCollection->addFieldToFilter('entity_id', $productIds);
        }

        $rule->getConditions()->collectValidatedAttributes($productCollection);

        $this->iterator->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'product' => $this->productFactory->create(),
                'rule'    => $rule,
            ]
        );

        $this->indexer->process($rankingFactor, $productIds, function () {
            foreach ($this->productIds as $id => $value) {
                $score = $value ? IndexInterface::MAX : 0;

                $this->indexer->add($id, $score, (string)$value);
            }
        });
    }

    /** Callback function for product matching */
    public function callbackValidateProduct(array $args): void
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $rule = $args['rule'];

        $this->productIds[$product->getId()] = $rule->getConditions()->validate($product);
    }
}
