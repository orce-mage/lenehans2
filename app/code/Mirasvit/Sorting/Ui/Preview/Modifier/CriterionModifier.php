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
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Mirasvit\Sorting\Ui\Preview\CollectionScoreService;

class CriterionModifier
{
    private $request;

    private $criterionRepository;

    private $rankingFactorRepository;

    private $collectionScoreService;

    public function __construct(
        RequestInterface $request,
        CriterionRepository $criterionRepository,
        RankingFactorRepository $rankingFactorRepository,
        CollectionScoreService $collectionScoreService
    ) {
        $this->request                 = $request;
        $this->criterionRepository     = $criterionRepository;
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->collectionScoreService  = $collectionScoreService;
    }

    public function modifyCollection(AbstractCollection $collection): array
    {
        $crData = $this->request->getParam('criterion');

        if (!isset($crData[CriterionInterface::CONDITIONS])) {
            return [];
        }

        $conditions = $crData[CriterionInterface::CONDITIONS];

        if (!is_array($conditions)) {
            return [];
        }

        $conditionCluster = new Criterion\ConditionCluster();
        $conditionCluster->loadArray($conditions);

        $ids = [];

        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::IS_GLOBAL, 1);

        foreach ($rankingFactors as $factor) {
            $ids[] = $factor->getId();
        }

        foreach ($conditionCluster->getFrames() as $frame) {
            foreach ($frame->getNodes() as $node) {
                if ($node->getSortBy() == CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR) {
                    $ids[] = $node->getRankingFactor();
                }
            }
        }

        $criterion = $this->criterionRepository->create();
        $criterion->setConditionCluster($conditionCluster);

        $this->collectionScoreService->sortCollection($collection, $rankingFactors->getItems(), $criterion);

        # prevent "random" sorting, if scores are same
        $collection->setOrder('entity_id');

        return $ids;
    }
}
