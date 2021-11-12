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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Service\FilterCriteria\FilterCriteria;

class FilterService
{
    private $coreRegistry;

    private $filterRepository;

    private $filterOptionRepository;

    private $resource;

    public function __construct(
        FilterRepository $filterRepository,
        FilterOptionRepository $filterOptionRepository,
        Registry $coreRegistry,
        ResourceConnection $resource
    ) {
        $this->filterRepository       = $filterRepository;
        $this->filterOptionRepository = $filterOptionRepository;
        $this->coreRegistry           = $coreRegistry;
        $this->resource               = $resource;
    }

    /**
     * @return FilterOptionInterface[]
     */
    public function getOptions(FilterInterface $filter, FilterCriteria $searchCriteria): array
    {
        $criteria = clone $searchCriteria;

        foreach ($searchCriteria->getFilters() as $k => $v) {
            $searchFilter = $this->filterRepository->get($v->getFilterId());

            if ($searchFilter->getPosition() >= $filter->getPosition()) {
                $criteria->removeFilter($k);
            }
        }

        $collection = $this->filterOptionRepository->getCollection();
        $collection->addFieldToFilter(FilterOptionInterface::FILTER_ID, $filter->getId());

        $optionIds   = $this->getOptionIds($filter, $criteria);
        $optionIds[] = 0;

        $collection->addFieldToFilter(FilterOptionInterface::ID, ['in' => $optionIds]);

        if ($filter->getSortMode() === FilterInterface::SORT_MODE_ASC_STRING) {
            $collection->setOrder(FilterOptionInterface::NAME, 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_ASC_INT) {
            $collection->setOrder('CAST(' . FilterOptionInterface::NAME . ' as DECIMAL)', 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_DESC_STRING) {
            $collection->setOrder(FilterOptionInterface::NAME, 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_DESC_INT) {
            $collection->setOrder('CAST(' . FilterOptionInterface::NAME . ' as DECIMAL)', 'desc');
        }

        return $collection->getItems();
    }

    /**
     * @return FilterOptionInterface[]
     */
    public function getOptionsByFilter(FilterInterface $filter): array
    {
        $collection = $this->filterOptionRepository->getCollection();
        $collection->addFieldToFilter(FilterOptionInterface::FILTER_ID, $filter->getId());

        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        $select = $connection->select()->from($indexTable, [IndexInterface::OPTION_ID])
            ->where(IndexInterface::FILTER_ID . ' = ?', $filter->getId())
            ->group(IndexInterface::OPTION_ID);

        $optionIds   = $connection->fetchCol($select);

        $optionIds[] = 0;

        $collection->addFieldToFilter(FilterOptionInterface::ID, ['in' => $optionIds]);

        if ($filter->getSortMode() === FilterInterface::SORT_MODE_ASC_STRING) {
            $collection->setOrder(FilterOptionInterface::NAME, 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_ASC_INT) {
            $collection->setOrder('CAST(' . FilterOptionInterface::NAME . ' as DECIMAL)', 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_DESC_STRING) {
            $collection->setOrder(FilterOptionInterface::NAME, 'asc');
        } elseif ($filter->getSortMode() === FilterInterface::SORT_MODE_DESC_INT) {
            $collection->setOrder('CAST(' . FilterOptionInterface::NAME . ' as DECIMAL)', 'desc');
        }

        return $collection->getItems();
    }

    public function getMatchedProductIds(FilterCriteria $searchCriteria): array
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->coreRegistry->registry('current_category');

        if (count($searchCriteria->getFilters()) === 0 && (!$category || !$category->getId())) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        $select = $connection->select()
            ->from(['f' => $indexTable], ['product_id'])
            ->group('product_id');

        $where  = [];
        foreach ($searchCriteria->getFilters() as $filter) {
            $filterId = $filter->getFilterId();

            if (!isset($where[$filterId])) {
                $where[$filterId] = [];
            }

            $where[$filterId] = array_merge($where[$filterId], $filter->getOptionIds());
        }

        foreach ($where as $filterId => $optionIds) {
            $select->joinLeft(['f' . $filterId => $indexTable], 'f' . $filterId . '.product_id = f.product_id', [])
                ->where('f' . $filterId . '.option_id IN (' . implode(',', $optionIds) . ')');
        }

        if ($category && $category->getId()) {
            $productsSelect = $category->getProductCollection()->getSelect();
            $productsSelect->reset(\Zend_Db_Select::COLUMNS);
            $productsSelect->columns('e.entity_id');

            $select->where('f' . '.product_id IN (' . $productsSelect->__toString() . ')');
        }

        return $connection->fetchCol($select);
    }

    private function getOptionIds(FilterInterface $filter, FilterCriteria $searchCriteria): array
    {
        $connection = $this->resource->getConnection();
        $indexTable = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        $select = $connection->select()->from($indexTable, [IndexInterface::OPTION_ID])
            ->where(IndexInterface::FILTER_ID . ' = ?', $filter->getId())
            ->group(IndexInterface::OPTION_ID);

        $productIds = $this->getMatchedProductIds($searchCriteria);

        // for the first filter we load all options. It occurs on the result page.
        if ($productIds) {
            $select->where('product_id IN (?)', $productIds);
        }

        // for the first filter we load all options. It occurs on the result page.
        if (count($searchCriteria->getFilters()) > 0) {
            $where = [];
            foreach ($searchCriteria->getFilters() as $k => $v) {
                $searchFilter = $this->filterRepository->get($v->getFilterId());

                if ($v->getPath() && $searchFilter->getPosition() == $filter->getPosition() - 1) {
                    $where[] = 'path LIKE ' . $select->getConnection()->quote($v->getPath() . '/%');
                }
            }

            if ($where) {
                $where[] = 'path is NULL';

                $select->where(implode(' OR ', $where));
            }
        }

        return $connection->fetchCol($select);
    }
}
