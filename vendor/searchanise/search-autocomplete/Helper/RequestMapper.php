<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Match as MatchExpression;
use Magento\Framework\Search\Request\Query\Filter as FilterExpression;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Filter\Wildcard;
use Magento\Framework\Search\Request\Filter\Range as RangeFilter;

/**
 * Searchanise request mapper for ElasticSearch
 */
class RequestMapper extends AbstractHelper
{
    public function buildQuery(RequestInterface $request)
    {
        $searchaniseQuery = [
            'query'        => '',
            'orders'       => [],
            'filters'      => [],
            'queryFilters' => [],
        ];

        $query = $request->getQuery();
        $from  = $request->getFrom();
        $size  = $request->getSize();
        $sort  = $request->getSort();

        $this->processQuery($query, $searchaniseQuery);

        foreach ($sort as $s) {
            $searchaniseQuery['orders'][$s['field']] = strtolower($s['direction']);
            break; // Searchanise supports only single sort
        }

        $searchaniseQuery['pageSize'] = $size;
        $searchaniseQuery['curPage']  = ceil($from / $size) + 1;

        return $searchaniseQuery;
    }

    protected function processQuery($query, array &$result)
    {
        if ($query instanceof BoolExpression) {
            $this->processBoolExpression(
                $query->getShould(),
                $query->getMust(),
                $query->getMustNot(),
                $result
            );
        }

        if ($query instanceof MatchExpression) {
            $this->processMatchExpression(
                $query->getName(),
                $query->getValue(),
                $query->getMatches(),
                $result
            );
        }

        if ($query instanceof FilterExpression) {
            $this->processFilterExpression(
                $query->getName(),
                $query->getReference(),
                $result
            );
        }
    }

    protected function processBoolExpression(array $should, array $must, array $mustNot, array &$result)
    {
        foreach ($should as $name => $conditionQuery) {
            $this->processQuery($conditionQuery, $result);
        }

        foreach ($must as $name => $conditionQuery) {
            $this->processQuery($conditionQuery, $result);
        }

        // TODO: Not implemented
        /*foreach ($mustNot as $name => $conditionQuery) {

        }*/
    }

    protected function processMatchExpression($name, $value, array $matches, array &$result)
    {
        if ($name == 'search' || $name == 'partial_search') {
            $result['query'] = $value;
        } elseif (preg_match('/^(.*)_query$/', $name, $matches)) {
            $name = $matches[1];

            if ($name == 'description') {
                $name = 'full_description';
            }

            $result['filters'][$name]['like'] = $value;
        }
    }

    protected function processFilterExpression($name, $term, array &$result)
    {
        if ($term instanceof Term) {
            $field = $term->getField();
            $value = $term->getValue();

            $result['filters'][$field] = $value;
        }

        if ($term instanceof Wildcard) {
            $field = $term->getField();
            $value = $term->getValue();

            $result['filters'][$field]['like'] = $value;
        }

        if ($term instanceof RangeFilter) {
            $field = $term->getField();
            $from = $term->getFrom();
            $to = $term->getTo();

            if (!empty($from)) {
                $result['filters'][$field]['from'] = $from;
            }

            if (!empty($to)) {
                $result['filters'][$field]['to'] = $to;
            }
        }
    }
}
