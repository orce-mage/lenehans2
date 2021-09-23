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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Ui\Page;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;

class PageDataProvider extends DataProvider
{
    private $pageService;

    public function __construct(
        PageServiceInterface $pageService,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = []
    ) {
        $this->pageService = $pageService;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];

        /** @var PageInterface $page */
        foreach ($searchResult->getItems() as $page) {
            $this->pageService->isCached($page);
            $itemData = $page->getData();
            $itemData['status'] = $page->getStatus();
//            $itemData['status']    = $this->pageService->isCached($page) ? 'in cache' : 'pending';
            $itemData['vary_data'] = $this->renderVaryData($page);

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    /**
     * @param PageInterface $page
     * @return string
     */
    public function renderVaryData(PageInterface $page)
    {
        $data                = $page->getVaryData();
        $data['product_id']  = $page->getProductId();
        $data['category_id'] = $page->getCategoryId();

        $html = $this->arrayToTable($data);
        $html .= "id:".$page->getCacheId()."<br>";
        return $html;
    }

    /**
     * @param array $data
     * @return string
     */
    private function arrayToTable($data)
    {
        $html = '<table class="cache-warmer__grid-table">';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if ($value) {
                $html .= '<tr>';
                $html .= '<td>' . $key . '</td>';
                $html .= '<td>' . $value . '</td>';
                $html .= '</tr> ';
            }
        }
        $html .= '</table > ';

        return $html;
    }
}
