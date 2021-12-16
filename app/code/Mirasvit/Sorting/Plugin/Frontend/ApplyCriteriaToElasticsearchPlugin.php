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

namespace Mirasvit\Sorting\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State as AppState;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

/**
 * @see \Magento\Framework\Api\SearchCriteria::setSortOrders()
 * @SuppressWarnings(PHPMD)
 */
class ApplyCriteriaToElasticsearchPlugin
{
    private $config;

    private $criterionRepository;

    private $rankingFactorRepository;

    /** @var \Magento\Framework\App\Request\Http */
    private $request;

    private $appState;

    public function __construct(
        ConfigProvider $config,
        CriterionRepository $criterionRepository,
        RankingFactorRepository $rankingFactorRepository,
        RequestInterface $request,
        AppState $appState
    ) {
        $this->config                  = $config;
        $this->criterionRepository     = $criterionRepository;
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->request                 = $request;
        $this->appState                = $appState;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteria $subject
     * @param array                                 $orders
     *
     * @return array
     */
    public function beforeSetSortOrders($subject, $orders): array
    {
        if (!$this->shouldAffectOrders()) {
            return [$orders];
        }

        if (!is_array($orders)) {
            $orders = [];
        }
        $newOrders = [];

        $this->addOrder($newOrders, 'sorting_global', 'DESC');

        foreach ($orders as $attr => $spec) {
            if (is_object($spec)) { //rest api request
                /** @var \Magento\Framework\Api\SortOrder $spec */

                $attr      = $spec->getField();
                $direction = $spec->getDirection();
            } else {
                $direction = $spec;
            }

            $criterion = $this->criterionRepository->getByCode($attr);

            if ($criterion) {
                foreach ($this->getFrames($criterion, $direction) as $frame => $dir) {
                    $this->addOrder($newOrders, $frame, $dir);
                }
            }
        }

        //restore original order
        foreach ($orders as $attr => $direction) {
            if (is_numeric($attr)) {
                $newOrders[] = $direction;
            } else {
                $newOrders[$attr] = $direction;
            }
        }

        return [$newOrders];
    }

    private function getFrames(CriterionInterface $criterion, string $direction): array
    {
        $frameScores = [];

        foreach ($criterion->getConditionCluster()->getFrames() as $frameIdx => $frame) {
            if (count($frame->getNodes()) >= 2) {
                $key = 'sorting_criterion_' . $criterion->getId() . '_frame_' . $frameIdx;

                $frameScores[$key] = $frameIdx === 0 ? $direction : $frame->getDirection();
            } else {
                foreach ($frame->getNodes() as $node) {
                    if ($node->getSortBy() === CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR) {
                        $key = 'sorting_factor_' . $node->getRankingFactor();
                    } else {
                        $key = $node->getAttribute();
                    }

                    $frameScores[$key] = $frameIdx === 0 ? $direction : $frame->getDirection();;
                }
            }
        }

        return $frameScores;
    }

    private function addOrder(array &$orderList, string $attr, string $direction): void
    {
        if ($this->appState->getAreaCode() === 'webapi_rest') {
            $orderList[] = new \Magento\Framework\Api\SortOrder([
                'field'     => $attr,
                'direction' => $direction,
            ]);
        } else {
            $orderList[$attr] = $direction;
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function shouldAffectOrders()
    {
        if ($this->config->isElasticSearch() == false) {
            return false;
        }

        if ($this->appState->getAreaCode() === 'webapi_rest'
            && (strpos($this->request->getPathInfo(), 'rest/V1/products') === false
                || strpos($this->request->getPathInfo(), 'rest/V1/products/attribute-sets') !== false)
        ) {
            return false;
        }

        return true;
    }
}
