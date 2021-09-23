<?php

namespace Searchanise\SearchAutocomplete\Plugins;

use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Model\Configuration;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation as CollectionProductLimitation;

/**
 * Product limitation helper class
 */
class ProductLimitation
{
    /**
     * Configuration
     */
    private $configuration;

    /**
     * @var SearchaniseHelper
     */
    private $searchaniseHelper;

    public function __construct(
        Configuration $configuration,
        SearchaniseHelper $searchaniseHelper
    ) {
        $this->configuration = $configuration;
        $this->searchaniseHelper = $searchaniseHelper;
    }

    /**
     * Replace using price index
     *
     * @param CollectionProductLimitation $instance
     * @param bool                        $result
     *
     * @return bool
     */
    public function afterisUsingPriceIndex(CollectionProductLimitation $instance, $result) {
        if (
            $this->configuration->getCurrentEngine() == 'elasticsearch7'
            && $this->searchaniseHelper->getSearchaniseRequest() !== null
            && $this->searchaniseHelper->getSearchaniseRequest()->getSortOrder()[0] == 'price'
        ) {
            // We have to override using price index for sorting
            $result = false;
        }

        return $result;
    }
}
