<?php

namespace MageBig\AjaxFilter\Plugin\CatalogSearch\Model\Search;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

class IndexBuilder
{
    /**
     * @var FilterMapper
     */
    private $fieldMapper;

    /**
     * IndexBuilder constructor.
     * @param FilterMapper $fieldMapper
     */
    public function __construct(
        FilterMapper $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * Build index query
     *
     * @param $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundBuild($subject, callable $proceed, RequestInterface $request)
    {
        $select = $proceed($request);
        $filters = $this->getFilters($request->getQuery());
        foreach ($filters as $filter) {
            $this->fieldMapper->apply($filter, $select);
        }

        return $select;
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    // @codingStandardsIgnoreLine
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                // @codingStandardsIgnoreLine
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }
}
