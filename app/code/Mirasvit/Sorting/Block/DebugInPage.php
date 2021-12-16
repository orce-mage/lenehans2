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

namespace Mirasvit\Sorting\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Block\Product\ImageBuilderFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\View\Element\Template;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Mirasvit\Sorting\Service\DebugService;
use Mirasvit\Sorting\Service\ScoreFetcherService;

class DebugInPage extends Template
{
    const IMAGE_DISPLAY_AREA = 'category_page_grid';

    protected $_template = "Mirasvit_Sorting::debug_in_page.phtml";

    private   $debugService;

    /** @var \Magento\Catalog\Block\Product\ImageBuilder */
    private $imageBuilder;

    private $scoreFetcherService;

    private $rankingFactorRepository;

    public function __construct(
        DebugService $debugService,
        ImageBuilderFactory $imageBuilderFactory,
        ScoreFetcherService $scoreFetcherService,
        RankingFactorRepository $rankingFactorRepository,
        Template\Context $context,
        array $data = []
    ) {
        $this->debugService            = $debugService;
        $this->imageBuilder            = $imageBuilderFactory->create();
        $this->scoreFetcherService     = $scoreFetcherService;
        $this->rankingFactorRepository = $rankingFactorRepository;

        parent::__construct($context, $data);
    }

    public function getLogs(): array
    {
        return $this->debugService->getLogs();
    }

    public function getProductImageUrl(ProductInterface $product): string
    {
        return $this->imageBuilder->create($product, self::IMAGE_DISPLAY_AREA)->getImageUrl();
    }

    public function getProductPrice(ProductInterface $product): string
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);

        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container'     => true,
                    'display_minimal_price' => true,
                    'zone'                  => Render::ZONE_ITEM_LIST,
                    'list_category_page'    => true,
                ]
            );
        }

        return $price;
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    public function getScoresByProduct(ProductInterface $product): array
    {
        $scoreData = [
            'global'    => [],
            'criterion' => [],
        ];

        $productId = (int)$product->getId();

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

            $score = (float)$this->getScore($scores, $productId, Indexer::getScoreColumn($factor));
            $value = $this->getScore($scores, $productId, Indexer::getValueColumn($factor));
            $value = $value ? : 0;
            $name  = $factor->getName() . ' [' . $factor->getId() . ']';

            $scoreData['global'][] = [
                'name'   => $name,
                'score'  => $score,
                'value'  => $value,
                'weight' => $factor->getWeight(),
            ];
        }

        $criterion = $this->debugService->getCurrentCriterion();

        if (!$criterion) {
            return $scoreData; // or without global
        }

        $i = 1;
        foreach ($criterion->getConditionCluster()->getFrames() as $frame) {
            $criterionScoreData = [];

            foreach ($frame->getNodes() as $node) {
                if ($node->getSortBy() == 'attribute') {
                    $scope = 'criterion' . $i;

                    $criterionScoreData[] = [
                        'name'  => $node->getAttribute(),
                        'score' => null,
                    ];
                } else {
                    $scope = 'frame' . $i;
                    foreach ($factors as $factor) {
                        if ($node->getRankingFactor() == $factor->getId()) {
                            $score = (float)$this->getScore($scores, $productId, Indexer::getScoreColumn($factor));
                            $value = $this->getScore($scores, $productId, Indexer::getValueColumn($factor));
                            $value = $value ? : 0;
                            $name  = $factor->getName() . ' [' . $factor->getId() . ']';

                            $criterionScoreData[] = [
                                'name'   => $name,
                                'score'  => $score,
                                'value'  => $value,
                                'weight' => $node->getWeight(),
                            ];
                        }
                    }
                }

                $scoreData['criterion'][$scope] = $criterionScoreData;
            }

            $i++;
        }

        return $scoreData;
    }

    public function getScoresBlockHtml(ProductInterface $product, array $data): string
    {
        $data['product'] = $product;

        return $this->getLayout()->createBlock(DebugInPageScore::class, '', ['data' => $data])->toHtml();
    }

    public function getTotalScore(array $scoreData): string
    {
        $score = 0;

        foreach ($scoreData as $data) {
            if (!is_null($data['score'])) {
                $score += $data['score'] * $data['weight'];
            }
        }

        return number_format((float)$score, 3, '.', ' ');
    }

    public function getScopeLabel(string $scope): string
    {
        if ($scope == 'frame1') {
            return (string)__("Sort By");
        }

        return (string)__("Then Sort By");
    }

    protected function _toHtml(): string
    {
        if (!$this->debugService->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    private function getScore(array $scores, int $productId, string $columnName): ?string
    {
        if (isset($scores[$productId]) && isset($scores[$productId][$columnName])) {
            return (string)$scores[$productId][$columnName];
        }

        return null;
    }
}
