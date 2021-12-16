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

namespace Mirasvit\Sorting\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Sorting\Model\Indexer;

/**
 * @see \Magento\Catalog\Model\ResourceModel\Product::save()
 */
class ReindexProduct
{
    private $indexerRegistry;

    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    public function aroundSave(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    public function aroundDelete(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    private function reindexRow(int $productId)
    {
        $indexer = $this->indexerRegistry->get(Indexer::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    private function addCommitCallback(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            $productResource->addCommitCallback(function () use ($product) {
                $this->reindexRow((int)$product->getEntityId());
            });
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }
}
