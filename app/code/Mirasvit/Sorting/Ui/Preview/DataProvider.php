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

namespace Mirasvit\Sorting\Ui\Preview;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Mirasvit\Sorting\Service\ScoreFetcherService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends ProductDataProvider
{
    private $productCollectionFactory;

    private $context;

    private $rankingFactorRepository;

    private $scoreFetcherService;

    private $rankingFactorModifier;

    private $criterionModifier;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Modifier\RankingFactorModifier $rankingFactorModifier,
        Modifier\CriterionModifier $criterionModifier,
        ProductCollectionFactory $productCollectionFactory,
        RankingFactorRepository $rankingFactorRepository,
        ContextInterface $context,
        ScoreFetcherService $scoreFetcherService,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->rankingFactorModifier    = $rankingFactorModifier;
        $this->criterionModifier        = $criterionModifier;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->context                  = $context;
        $this->rankingFactorRepository  = $rankingFactorRepository;
        $this->scoreFetcherService      = $scoreFetcherService;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
    }

    public function getData(): array
    {
        $usedFactorIds = array_merge(
            $this->criterionModifier->modifyCollection($this->getCollection()),
            $this->rankingFactorModifier->modifyCollection($this->getCollection())
        );

        $data = parent::getData();
        $data = $this->addRankingFactorsToData($data, $usedFactorIds);

        $keys = [
            'sorting_score_global',
            'sorting_score_0',
            'sorting_score_1',
            'sorting_score_2',
            'sorting_score_3',
        ];

        foreach ($data['items'] as &$item) {
            foreach ($keys as $key) {
                if (isset($item[$key])) {
                    $item[$key] = round($item[$key], 2);
                }
            }
        }

        return $data;
    }

    /**
     * Native sorting is useless
     * {@inheritDoc}
     */
    public function addOrder($field, $direction)
    {
        return $this;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection */
        $collection = parent::getCollection();
        $collection->addFieldToSelect('status');

        return $collection;
    }

    private function addRankingFactorsToData(array $data, array $rankingFactorIds): array
    {
        if (!count($rankingFactorIds)) {
            return $data;
        }

        $items = [];
        foreach ($data['items'] as $item) {
            $items[$item['entity_id']] = $item;
        }

        $scoresData = $this->scoreFetcherService->getProductsScoreList(array_keys($items));

        foreach ($rankingFactorIds as $factorId) {
            $factor = $this->rankingFactorRepository->get($factorId);

            #set zero score
            foreach (array_keys($items) as $productId) {
                $items[$productId]['factors'][$factorId] = [
                    'label' => $factor->getName(),
                    'score' => 0,
                ];
            }

            #set real score
            foreach ($scoresData as $productId => $scoreData) {
                $items[$productId]['factors'][$factorId]['score'] = $scoreData[Indexer::getScoreColumnById($factorId)];
                $items[$productId]['factors'][$factorId]['value'] = $scoreData[Indexer::getValueColumnById($factorId)];
            }
        }

        foreach (array_keys($items) as $productId) {
            $items[$productId]['factors'] = array_values($items[$productId]['factors']);
        }

        $data['items'] = array_values($items);

        return $data;
    }
}
