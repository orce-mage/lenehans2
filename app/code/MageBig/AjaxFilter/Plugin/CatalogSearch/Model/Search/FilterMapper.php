<?php

namespace MageBig\AjaxFilter\Plugin\CatalogSearch\Model\Search;

use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\StoreManagerInterface;

class FilterMapper
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     */
    private $validFields = ['rating'];

    /**
     * FilterMapper constructor.
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param FilterInterface $filter
     * @param Select $select
     * @return bool
     * @throws NoSuchEntityException
     */
    public function apply(
        FilterInterface $filter,
        Select $select
    ) {
        if (!in_array($filter->getField(), $this->validFields, true)) {
            return false;
        }

        switch ($filter->getField()) {
            case 'rating':
                $isApplied = $this->applyRatingFilter($filter, $select);
                break;
            default:
                $isApplied = false;
        }

        return $isApplied;
    }

    /**
     * Applies rating filter
     *
     * @param FilterInterface $filter
     * @param Select $select
     * @return bool
     * @throws NoSuchEntityException
     */
    private function applyRatingFilter(
        FilterInterface $filter,
        Select $select
    ) {
        $alias = $filter->getField() . RequestGenerator::FILTER_SUFFIX;
        $storeID = $this->storeManager->getStore()->getId();
        $ratingTable = $this->resourceConnection->getTableName('review_entity_summary');
        $connection = $this->resourceConnection->getConnection();

        $rating = $connection->select()
            ->from($ratingTable, ['entity_pk_value', 'entity_type', 'store_id', 'rating_summary AS rating'])
            ->where('entity_type = ?', 1)
            ->where('store_id = ?', $storeID);
        $select->joinLeft(
            [$alias => $rating],
            '`rating_filter`.`entity_pk_value`=`search_index`.entity_id',
            []
        );

        return true;
    }
}
