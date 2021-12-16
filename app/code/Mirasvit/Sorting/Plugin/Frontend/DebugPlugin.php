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

use Magento\Catalog\Model\Product;
use Magento\Framework\View\LayoutInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Block\DebugInCard;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Mirasvit\Sorting\Service\CriteriaApplierService;
use Mirasvit\Sorting\Service\ScoreFetcherService;

/**
 * @see \Magento\Catalog\Block\Product\ListProduct::getAddToCartPostParams()
 * @see \Magento\Catalog\Block\Product\ListProduct::getProductPrice()
 * @see \Magento\Catalog\Block\Product\ListProduct::getProductPriceHtml()
 * @see \Magento\CatalogWidget\Block\Product\ProductsList::getAddToCartPostParams()
 * @see \Magento\CatalogWidget\Block\Product\ProductsList::getProductPrice()
 * @see \Magento\CatalogWidget\Block\Product\ProductsList::getProductPriceHtml()
 */
class DebugPlugin
{
    private $config;

    private $rankingFactorRepository;

    private $scoreFetcherService;

    private $layout;

    private $criteriaApplierService;

    private $weights     = [];

    private $scores      = [];

    private $values      = [];

    private $renderedIds = [];

    public function __construct(
        ConfigProvider $config,
        RankingFactorRepository $rankingFactorRepository,
        ScoreFetcherService $scoreFetcherService,
        LayoutInterface $layout,
        CriteriaApplierService $criteriaApplierService
    ) {
        $this->config                  = $config;
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->scoreFetcherService     = $scoreFetcherService;
        $this->layout                  = $layout;
        $this->criteriaApplierService  = $criteriaApplierService;

        foreach (['global', 'criterion', 'frame1', 'frame2'] as $k) {
            $this->scores[$k]  = [];
            $this->values[$k]  = [];
            $this->weights[$k] = [];
        }
    }

    /**
     * @param mixed   $subject
     * @param string  $html
     * @param Product $product
     *
     * @return string
     */
    public function afterGetProductPrice($subject, string $html, $product = null): string
    {
        return $this->render($product) . $html;
    }

    /**
     * @param mixed   $subject
     * @param string  $html
     * @param Product $product
     *
     * @return string
     */
    public function afterGetProductDetailsHtml($subject, string $html, $product = null): string
    {
        return $this->render($product) . $html;
    }

    /**
     * @param mixed   $subject
     * @param array   $params
     * @param Product $product
     *
     * @return array
     */
    public function afterGetAddToCartPostParams($subject, $params, $product = null)
    {
        if ($this->config->isDebug()) {
            //uncomment if other are not working
            //echo $this->render($product);
        }

        return $params;
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    private function render($product)
    {
        if (!$this->config->isDebug()) {
            return '';
        }

        if (!$product) {
            return '';
        }

        if (in_array($product->getId(), $this->renderedIds)) {
            return '';
        }

        $this->renderedIds[] = $product->getId();

        $productId = (int)$product->getId();
        $this->setScoreData($productId);

        return $this->layout->createBlock(DebugInCard::class, '', [
            'scores'  => $this->scores,
            'values'  => $this->values,
            'weights' => $this->weights,
            'product' => $product,
        ])->toHtml();
    }

    /** @SuppressWarnings(PHPMD) */
    private function setScoreData(int $productId): void
    {
        $scores = $this->scoreFetcherService->getProductsScoreList([$productId]);

        $factors = $this->rankingFactorRepository->getCollection()
            ->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, true);

        foreach ($factors as $factor) {
            if (!$factor->isGlobal()) {
                continue;
            }

            if (!$factor->getWeight()) {
                continue;
            }

            $score = $this->getScore($scores, $productId, Indexer::getScoreColumn($factor));
            $value = $this->getScore($scores, $productId, Indexer::getValueColumn($factor));

            $name = $factor->getName() . ' [' . $factor->getId() . ']';

            $this->scores['global'][$name]  = $score;
            $this->values['global'][$name]  = $value;
            $this->weights['global'][$name] = $factor->getWeight();
        }

        $criterion = $this->criteriaApplierService->getCurrentCriterion();
        if (!$criterion) {
            return;
        }

        $i = 1;
        foreach ($criterion->getConditionCluster()->getFrames() as $frame) {
            foreach ($frame->getNodes() as $node) {
                if ($node->getSortBy() == 'attribute') {
                    $this->scores['criterion' . $i][$node->getAttribute()] = "-";
                } else {
                    foreach ($factors as $factor) {
                        if ($node->getRankingFactor() == $factor->getId()) {
                            $score = $this->getScore($scores, $productId, Indexer::getScoreColumn($factor));
                            $value = $this->getScore($scores, $productId, Indexer::getValueColumn($factor));

                            $name = $factor->getName() . ' [' . $factor->getId() . ']';

                            $this->scores['frame' . $i][$name]  = $score;
                            $this->values['frame' . $i][$name]  = $value;
                            $this->weights['frame' . $i][$name] = $node->getWeight();
                        }
                    }
                }
            }
            $i++;
        }
    }


    private function getScore(array $scores, int $productId, string $columnName)
    {
        if (isset($scores[$productId]) && isset($scores[$productId][$columnName])) {
            return $scores[$productId][$columnName];
        }

        return null;
    }
}
