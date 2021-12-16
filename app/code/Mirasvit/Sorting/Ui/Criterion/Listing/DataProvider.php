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

namespace Mirasvit\Sorting\Ui\Criterion\Listing;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Sorting\Repository\CriterionPopularityRepository;

/** @SuppressWarnings(PHPMD) */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    private $criterionPopularityRepository;

    public function __construct(
        CriterionPopularityRepository $criterionPopularityRepository,
        string                        $name,
        string                        $primaryFieldName,
        string                        $requestFieldName,
        ReportingInterface            $reporting,
        SearchCriteriaBuilder         $searchCriteriaBuilder,
        RequestInterface              $request,
        FilterBuilder                 $filterBuilder,
        array                         $meta = [],
        array                         $data = []
    ) {
        $this->criterionPopularityRepository = $criterionPopularityRepository;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $arrItems = [];

        $arrItems['items'] = [];
        /** @var \Mirasvit\Sorting\Model\Criterion $item */
        foreach ($searchResult->getItems() as $item) {
            $itemData = $item->getData();

            $itemData['condition']  = $item->getConditionCluster()->toHtml();
            $itemData['popularity'] = $this->criterionPopularityRepository->getPopularity($item->getCode());

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
