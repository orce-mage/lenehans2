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

namespace Mirasvit\Sorting\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class Indexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    const INDEXER_ID = 'mst_sorting';

    private $resource;

    private $eventManager;

    private $rankingFactorRepository;

    public function __construct(
        ResourceConnection $resource,
        ManagerInterface $eventManager,
        RankingFactorRepository $rankingFactorRepository
    ) {
        $this->resource                = $resource;
        $this->eventManager            = $eventManager;
        $this->rankingFactorRepository = $rankingFactorRepository;
    }

    public static function getScoreColumn(RankingFactorInterface $rankingFactor): string
    {
        return self::getScoreColumnById($rankingFactor->getId());
    }

    public static function getScoreColumnById(int $id): string
    {
        return 'factor_' . $id . '_score';
    }

    public static function getValueColumn(RankingFactorInterface $rankingFactor): string
    {
        return self::getValueColumnById($rankingFactor->getId());
    }

    public static function getValueColumnById(int $id): string
    {
        return 'factor_' . $id . '_value';
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    public function executeFull()
    {
        $this->executeRankingFactor();

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
    }

    public function executeRankingFactor(array $rankingFactorIds = [], array $productIds = [])
    {
        $collection = $this->rankingFactorRepository->getCollection();

        if ($rankingFactorIds) {
            $collection->addFieldToFilter(RankingFactorInterface::ID, $rankingFactorIds);
        }

        foreach ($collection as $rankingFactor) {
            $factor = $this->rankingFactorRepository->getFactor($rankingFactor->getType());

            if ($factor) {
                $factor->reindex($rankingFactor, $productIds);
            }
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->executeRankingFactor([], $ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
    }

    public function getIdentities()
    {
        return [
            \Magento\Catalog\Model\Category::CACHE_TAG,
        ];
    }
}
