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

namespace Mirasvit\Finder\Model\Index;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Iterator;
use Mirasvit\Feed\Api\Data\RuleInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FilterRepository;

class Indexer
{
    private $eavConfig;

    private $filterOptionRepository;

    private $filterRepository;

    private $iterator;

    private $productCollectionFactory;

    private $resource;

    private $select;

    public function __construct(
        FilterRepository $filterRepository,
        ProductCollectionFactory $productCollectionFactory,
        Iterator $iterator,
        Select $select,
        EavConfig $eavConfig,
        FilterOptionRepository $filterOptionRepository,
        ResourceConnection $resource
    ) {
        $this->filterRepository         = $filterRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->iterator                 = $iterator;
        $this->select                   = $select;
        $this->eavConfig                = $eavConfig;
        $this->filterOptionRepository   = $filterOptionRepository;
        $this->resource                 = $resource;
    }

    public function reindex(FinderInterface $finder): void
    {
        $filters = $this->filterRepository->getCollection();
        $filters->addFieldToFilter(FilterInterface::FINDER_ID, $finder->getId());

        foreach ($filters as $filter) {
            if ($filter->getLinkType() === FilterInterface::LINK_TYPE_ATTRIBUTE) {
                $select = $this->resource->getConnection()->select()
                    ->from(
                        ['e' => $this->resource->getTableName('catalog_product_entity')],
                        ['entity_id']
                    )->joinLeft(
                        ['super' => $this->resource->getTableName('catalog_product_super_link')],
                        'super.product_id=e.entity_id',
                        ['parent_id']
                    );

                $this->select->joinField($select, $filter->getAttributeCode());

                $map = [];
                $this->iterator->walk($select, [function ($data) use ($filter, &$map) {
                    $row = $data['row'];

                    $code = $filter->getAttributeCode() == 'category_ids' ? 'name' : $filter->getAttributeCode();

                    $map[$row['entity_id']][] = $row[$code];

                    if ($row['parent_id']) {
                        $map[$row['parent_id']][] = $row[$code];
                    }
                }], []);

                $attribute = $this->eavConfig->getAttribute('catalog_product', $filter->getAttributeCode());

                $sourceOptions = [];
                foreach ($attribute->getOptions() as $option) {
                    $sourceOptions[(int)$option->getValue()] = (string)$option->getLabel();
                }

                if (count($sourceOptions)) {
                    foreach ($map as $productId => $options) {
                        $map[$productId] = [];
                        foreach ($options as $optIds) {
                            foreach (explode(',', $optIds) as $optId) {
                                $optId = (int)$optId;
                                if ($optId && isset($sourceOptions[$optId])) {
                                    $map[$productId][] = $sourceOptions[$optId];
                                }
                            }
                        }
                    }
                }

                $toIndex = [];
                foreach ($map as $productId => $labels) {
                    foreach ($labels as $label) {
                        $option = $this->ensureOption($finder, $filter, (string)$label);

                        $toIndex[] = [
                            IndexInterface::PRODUCT_ID => $productId,
                            IndexInterface::FINDER_ID  => $finder->getId(),
                            IndexInterface::FILTER_ID  => $filter->getId(),
                            IndexInterface::OPTION_ID  => $option->getId(),
                        ];
                    }
                }

                $this->clearIndex($finder, $filter);
                $this->addIndex($toIndex);
            }
        }
    }

    private function clearIndex(FinderInterface $finder, FilterInterface $filter): void
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        $connection->delete($table, [
            IndexInterface::FINDER_ID . ' = ?' => $finder->getId(),
            IndexInterface::FILTER_ID . ' = ?' => $filter->getId(),
        ]);
    }

    private function addIndex(array $rows)
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName(IndexInterface::TABLE_NAME);

        foreach ($rows as $row) {
            $connection->insert($table, $row);
        }
    }

    private function ensureOption(FinderInterface $finder, FilterInterface $filter, string $name): FilterOptionInterface
    {
        $collection = $this->filterOptionRepository->getCollection();
        $collection->addFieldToFilter(FilterOptionInterface::FINDER_ID, $finder->getId())
            ->addFieldToFilter(FilterOptionInterface::FILTER_ID, $filter->getId())
            ->addFieldToFilter(FilterOptionInterface::NAME, $name);

        $option = $collection->getFirstItem();

        if (!$option->getId()) {
            $option = $this->filterOptionRepository->create()
                ->setFinderId($finder->getId())
                ->setFilterId($filter->getId())
                ->setName($name);

            $this->filterOptionRepository->save($option);
        }

        return $option;
    }
}
