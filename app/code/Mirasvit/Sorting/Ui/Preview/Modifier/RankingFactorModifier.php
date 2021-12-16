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

namespace Mirasvit\Sorting\Ui\Preview\Modifier;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Criterion;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;
use Mirasvit\Sorting\Ui\Preview\CollectionScoreService;

class RankingFactorModifier
{
    private $request;

    private $criterionRepository;

    private $collectionScoreService;

    public function __construct(
        RequestInterface $request,
        CriterionRepository $criterionRepository,
        CollectionScoreService $collectionScoreService
    ) {
        $this->request                = $request;
        $this->criterionRepository    = $criterionRepository;
        $this->collectionScoreService = $collectionScoreService;
    }

    public function modifyCollection(AbstractCollection $collection): array
    {
        $rfData = $this->request->getParam('rankingFactor');

        if (!isset($rfData[RankingFactorInterface::ID])) {
            return [];
        }

        $id = (int)$rfData[RankingFactorInterface::ID];

        if (!$id) {
            return [];
        }

        $cluster = new Criterion\ConditionCluster();

        $frame = new Criterion\ConditionFrame();
        $cluster->addFrame($frame);

        $node = new Criterion\ConditionNode();
        $frame->addNode($node);

        $node->setSortBy(CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR)
            ->setRankingFactor($id)
            ->setDirection('desc')
            ->setWeight((int)$rfData['weight']);

        $criterion = $this->criterionRepository->create();
        $criterion->setConditionCluster($cluster);

        $collection->setFlag(CriteriaApplierService::FLAG_CRITERION, $criterion);

        $this->collectionScoreService->sortCollection($collection, [], $criterion);

        # prevent "random" sorting, if scores are same
        $collection->setOrder('entity_id');

        return [$id];
    }
}
