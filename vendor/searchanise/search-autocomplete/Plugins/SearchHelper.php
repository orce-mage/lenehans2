<?php

namespace Searchanise\SearchAutocomplete\Plugins;

use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Helper\Data as SearchDataHelper;

/**
 * Search helper class
 */
class SearchHelper
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SearchaniseHelper
     */
    private $searchaniseHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Configuration $configuration,
        SearchaniseHelper $searchaniseHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->configuration = $configuration;
        $this->searchaniseHelper = $searchaniseHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Replace search result url with searchanise one
     *
     * @param SearchDataHelper $instance
     * @param string           $url
     *
     * @return string
     */
    public function afterGetResultUrl(SearchDataHelper $instance, $url)
    {
        if ($this->configuration->getIsResultsWidgetEnabled($this->storeManager->getStore()->getId())) {
            return $this->searchaniseHelper->getResultsFormPath();
        } else {
            return $url;
        }
    }
}
