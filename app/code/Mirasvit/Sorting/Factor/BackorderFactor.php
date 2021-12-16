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

use Magento\Framework\Module\Manager as ModuleManager;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class BackorderFactor implements FactorInterface
{
    private $context;

    private $indexer;

    private $moduleManager;

    public function __construct(
        Context       $context,
        FactorIndexer $indexer,
        ModuleManager $moduleManager
    ) {
        $this->context       = $context;
        $this->indexer       = $indexer;
        $this->moduleManager = $moduleManager;
    }

    public function getName(): string
    {
        return 'Backorders';
    }

    public function getDescription(): string
    {
        return 'Rank products based backorders: Allowed / Not Allowed.';
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
            ['entity_id', 'type_id']
        )->joinInner(
            ['sotck_item' => $resource->getTableName('cataloginventory_stock_item')],
            'sotck_item.product_id = e.entity_id',
            ['value' => 'backorders']
        )->group('e.entity_id');

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $stmt = $connection->query($select);

        $result = $stmt->fetchAll();

        $this->indexer->process($rankingFactor, $productIds, function () use ($result) {
            foreach ($result as $row) {
                $value = $row['value'];
                $score = $value ? IndexInterface::MAX : IndexInterface::MIN;

                $this->indexer->add((int)$row['entity_id'], $score, $value, (int)(isset($row['store_id']) ? $row['store_id'] : 0));
            }
        });
    }
}
