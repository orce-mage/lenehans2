<?php

namespace MageBig\AjaxFilter\Plugin\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Preprocessor
{
    private $validFields = ['rating'];

    private $connection;

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->connection = $resource->getConnection();
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundProcess(
        $subject,
        callable $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ) {
        if (in_array($filter->getField(), $this->validFields)) {
            $alias = $filter->getField() . RequestGenerator::FILTER_SUFFIX;
            return str_replace(
                $this->connection->quoteIdentifier($filter->getField()),
                $this->connection->quoteIdentifier($alias . '.' . $filter->getField()),
                $query
            );
        }

        return $proceed($filter, $isNegation, $query);
    }
}
