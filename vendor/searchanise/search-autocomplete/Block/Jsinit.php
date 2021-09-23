<?php

namespace Searchanise\SearchAutocomplete\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\View\Element\Template;
use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;

class Jsinit extends Template
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SearchaniseHelper
     */
    private $dataHelper;

    public function __construct(
        Context $context,
        ApiSeHelper $apiSeHelper,
        SearchaniseHelper $dataHelper,
        array $data = []
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    public function getWidgetLink()
    {
        $search_widgets_link = $this->apiSeHelper->getSearchWidgetsLink(false);
        return preg_replace('/(^https?:\/\/)(.*)(\.js)$/', '//$2', strtolower($search_widgets_link));
    }

    public function getConfiguration()
    {
        if (!$this->apiSeHelper->checkSearchaniseResult(true, null)) {
            return [];
        }

        $api_key = $this->apiSeHelper->getApiKey();
        $search_input_selector = $this->apiSeHelper->getSearchInputSelector();

        if (empty($search_input_selector)) {
            $search_input_selector = '#search';
        }

        $se_service_url = $this->apiSeHelper->getServiceUrl();
        $search_widgets_link = $this->apiSeHelper->getSearchWidgetsLink(false);
        $showOutOfStock = $this->apiSeHelper->getIsShowOutOfStockProducts();

        $price_format = $this->apiSeHelper->getPriceFormat();
        $min_price = $this->apiSeHelper->getCurLabelForPricesUsergroup();
        $result_from_path = $this->dataHelper->getResultsFormPath();
        $fallback_url = $this->getUrl('catalogsearch/result') . '?q=';

        $params = [
            'host' => $se_service_url,
            'api_key' => $api_key,
            'SearchInput' => $search_input_selector,
            'AdditionalSearchInputs' => '#name,#description,#sku',
            'AutoCmpParams' => [
                'union' => [
                    'price' => [
                        'min' => $min_price
                    ]
                ],
                'restrictBy' => [
                    'status' => 1,
                    'visibility' => '3|4'
                ]
            ],
            'options' => [
                'ResultsDiv' => '#snize_results',
                'ResultsFormPath' => $result_from_path,
                'ResultsFallbackUrl' => $fallback_url,

                'PriceFormat' => [
                    'decimals_separator' => $price_format['decimals_separator'],
                    'thousands_separator' => $price_format['thousands_separator'],
                    'symbol' => $price_format['symbol'],

                    'decimals' => $price_format['decimals'],
                    'rate' => $price_format['rate'],
                    'after' => $price_format['after']
                ]
            ],
            'ResultsParams' => [
                'facetBy' => [
                    'price' => [
                        'type' => 'slider'
                    ]
                ],
                'union' => [
                    'price' => [
                        'min' => $min_price
                    ]
                ],
                'restrictBy' => [
                    'status' => 1,
                    'visibility' => '3|4'
                ]
            ]
        ];

        if (!$showOutOfStock) {
            $params['AutoCmpParams']['restrictBy']['is_in_stock'] = '1';
            $params['ResultsParams']['restrictBy']['is_in_stock'] = '1';
        }

        return $params;
    }
}
