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

namespace Mirasvit\Sorting\Service;

use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Criterion\ConditionCluster;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class SampleService
{
    private $criterionRepository;

    private $rankingFactorRepository;

    private $indexer;

    public function __construct(
        RankingFactorRepository $rankingFactorRepository,
        CriterionRepository $criterionRepository,
        Indexer $indexer
    ) {
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->criterionRepository     = $criterionRepository;
        $this->indexer                 = $indexer;

    }

    public function getCriterionListFromJson(): array
    {
        $list = [];
        foreach (glob($this->getCriterionDir() . '/*.json') as $file) {
            try {
                $list[] = SerializeService::decode(file_get_contents($file));
            } catch (\Exception $e) {
            }
        }

        return $list;
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    public function addNewCriterion(string $code): ?int
    {
        $criterion = $this->criterionRepository->getByCode($code);

        if ($criterion) {
            return $criterion->getId();
        }

        $conditionCluster = new ConditionCluster();
        foreach ($this->getCriterionListFromJson() as $sampleData) {
            if ($sampleData['code'] != $code) {
                continue;
            }

            $conditionCluster->loadArray($sampleData['config_serialized']);
            $newRankingFactorIds = [];
            foreach ($conditionCluster->getFrames() as $frame) {
                foreach ($frame->getNodes() as $node) {
                    if ($node->getSortBy() == CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR) {
                        $factorTypeFromJson = (string)$node->getData('rankingFactor');

                        $rankingFactor      = $this->rankingFactorRepository->getByType($factorTypeFromJson);
                        if (!$rankingFactor->getId()) {
                            $rankingFactor = $this->addNewRankingFactor($factorTypeFromJson);
                            if ($rankingFactor->getId()) {
                                $newRankingFactorIds[] = $rankingFactor->getId();
                            }
                        }
                        $rankingFactorId = $rankingFactor->getId();
                        if (!$rankingFactorId) {
                            break 3;
                        }
                        $node->setRankingFactor($rankingFactorId);
                    }
                }
            }
            $criterion = $this->criterionRepository->create();
            $criterion->setName($sampleData[CriterionInterface::NAME])
                ->setCode($sampleData[CriterionInterface::CODE])
                ->setIsActive((bool)$sampleData[CriterionInterface::IS_ACTIVE])
                ->setIsDefault((bool)$sampleData[CriterionInterface::IS_DEFAULT])
                ->setIsSearchDefault((bool)$sampleData[CriterionInterface::IS_SEARCH_DEFAULT])
                ->setPosition((int)$sampleData[CriterionInterface::POSITION])
                ->setConditionCluster($conditionCluster);

            $criterion = $this->criterionRepository->save($criterion);

            $this->indexer->executeRankingFactor($newRankingFactorIds);

            return $criterion->getId();
        }

        return null;
    }

    public function addNewRankingFactor(string $type): RankingFactorInterface
    {
        $rankingFactor = $this->rankingFactorRepository->create();
        foreach ($this->getFactorListFromJson() as $sampleData) {
            if ($sampleData[RankingFactorInterface::TYPE] == $type) {
                $rankingFactor->setName($sampleData[RankingFactorInterface::NAME])
                    ->setType($sampleData[RankingFactorInterface::TYPE])
                    ->setIsActive((bool)$sampleData[RankingFactorInterface::IS_ACTIVE])
                    ->setIsGlobal((bool)$sampleData[RankingFactorInterface::IS_GLOBAL])
                    ->setWeight((int)$sampleData[RankingFactorInterface::WEIGHT])
                    ->setConfig($sampleData[RankingFactorInterface::CONFIG_SERIALIZED]);

                $rankingFactor = $this->rankingFactorRepository->save($rankingFactor);
                break;
            }
        }

        return $rankingFactor;
    }

    private function getFactorListFromJson(): array
    {
        $list = [];
        foreach (glob($this->getFactorDir() . '/*.json') as $file) {
            try {
                $list[] = SerializeService::decode(file_get_contents($file));
            } catch (\Exception $e) {
            }
        }

        return $list;
    }

    private function getCriterionDir(): string
    {
        return $this->getSetupDir() . '/Sample/Criterion';
    }

    private function getFactorDir(): string
    {
        return $this->getSetupDir() . '/Sample/Factor';
    }

    private function getSetupDir(): string
    {
        return dirname(dirname(__FILE__)) . '/Setup';
    }
}
