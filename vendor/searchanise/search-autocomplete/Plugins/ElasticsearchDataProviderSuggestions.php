<?php

namespace Searchanise\SearchAutocomplete\Plugins;

use Magento\Elasticsearch\Model\DataProvider\Base\Suggestions as ElasticsearchSuggestions;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryResultFactory;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Model\Configuration;

class ElasticsearchDataProviderSuggestions
{
    /**
     * Configuration
     */
    private $configuration;

    /**
     * @var SearchaniseHelper
     */
    private $searchaniseHelper;

    /**
     * QueryResultFactory
     */
    private $queryResultFactory;

    public function __construct(
        QueryResultFactory $queryResultFactory,
        Configuration $configuration,
        SearchaniseHelper $searchaniseHelper
    ) {
        $this->configuration = $configuration;
        $this->searchaniseHelper = $searchaniseHelper;
        $this->queryResultFactory = $queryResultFactory;
    }

    /**
     * Returns search suggestions
     *
     * @param ElasticsearchSuggestions $instance
     * @param callable                 $fn
     * @param QueryInterface           $query
     *
     * @return array
     */
    public function aroundGetItems(ElasticsearchSuggestions $instance, callable $fn, QueryInterface $query)
    {
        if ($this->searchaniseHelper->getSearchaniseRequest() !== null) {
            $result = [];
            $rawSuggestions = $this->searchaniseHelper->getRawSuggestions();

            foreach ($rawSuggestions as $k => $sug) {
                $result[] = $this->queryResultFactory->create([
                    'queryText'    => $sug,
                    'resultsCount' => $k,
                ]);
            }

            return $result;
        }

        return $fn($query);
    }
}
