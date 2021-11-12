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

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterfaceFactory;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;
use Mirasvit\Finder\Repository\FilterOptionRepository;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\IndexRepository;

class ImportOptionService
{
    private $connection;

    private $filterOptionInterfaceFactory;

    private $filterOptionRepository;

    private $filterRepository;

    private $productRepository;

    private $resource;

    private $cache = [];

    private $indexRepository;

    public function __construct(
        IndexRepository $indexRepository,
        FilterOptionInterfaceFactory $filterOptionInterfaceFactory,
        FilterOptionRepository $filterOptionRepository,
        FilterRepository $filterRepository,
        ProductRepository $productRepository,
        ResourceConnection $resource
    ) {
        $this->indexRepository              = $indexRepository;
        $this->filterOptionInterfaceFactory = $filterOptionInterfaceFactory;
        $this->filterOptionRepository       = $filterOptionRepository;
        $this->filterRepository             = $filterRepository;
        $this->productRepository            = $productRepository;

        $this->connection = $resource->getConnection();
        $this->resource   = $resource;
    }

    public function importFile(FinderInterface $finder, string $filePath, bool $isOverwrite): void
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception(sprintf("Can't read file %s", $filePath));
        }

        if ($isOverwrite) {
            $this->removeData($finder);
        }

        $filters = $this->filterRepository->getCollection()
            ->addFieldToFilter(FilterInterface::FINDER_ID, $finder->getId())
            ->setOrder(FilterInterface::POSITION, 'asc')
            ->getItems();

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $this->importRow($finder, $filters, $data);
        }

        fclose($handle);
    }

    private function removeData(FinderInterface $finder): void
    {
        $indexCollection = $this->indexRepository->getCollection()
            ->addFieldToFilter(IndexInterface::FINDER_ID, $finder->getId());
        foreach ($indexCollection as $index) {
            $this->indexRepository->delete($index);
        }

        $filterOptionCollection = $this->filterOptionRepository->getCollection()
            ->addFieldToFilter(FilterOptionInterface::FINDER_ID, $finder->getId());
        foreach ($filterOptionCollection as $filterOption) {
            $this->filterOptionRepository->delete($filterOption);
        }
    }

    private function importRow(FinderInterface $finder, array $filters, array $row): void
    {
        $sku = (string)$row[0];

        $productId = $this->getProductId($sku);

        if (!$productId) {
            return;
        }

        $path = '';

        $i = 1;
        foreach ($filters as $filter) {
            $optionName = $row[$i];

            $i++;

            $option = $this->ensureOption($finder, $filter, $optionName);

            $path .= '/' . $option->getId();

            $index = $this->indexRepository->create()
                ->setProductId($productId)
                ->setFinderId($finder->getId())
                ->setFilterId($filter->getId())
                ->setOptionId($option->getId())
                ->setPath($path);

            $this->indexRepository->save($index);
        }
    }

    private function ensureOption(FinderInterface $finder, FilterInterface $filter, string $optionName): FilterOptionInterface
    {
        $cacheKey = $filter->getId() . $optionName;

        if (!isset($this->cache[$cacheKey])) {
            /** @var FilterOptionInterface $option */
            $option = $this->filterOptionRepository->getCollection()
                ->addFieldToFilter(FilterOptionInterface::FINDER_ID, $finder->getId())
                ->addFieldToFilter(FilterOptionInterface::FILTER_ID, $filter->getId())
                ->addFieldToFilter(FilterOptionInterface::NAME, $optionName)
                ->getFirstItem();

            if (!$option->getId()) {
                $option = $this->filterOptionRepository->create();
                $option->setFinderId($finder->getId())
                    ->setFilterId($filter->getId())
                    ->setName($optionName);

                $this->filterOptionRepository->save($option);
            }

            $this->cache[$cacheKey] = $option;
        }

        return $this->cache[$cacheKey];
    }

    private function getProductId(string $sku): int
    {
        $row = $this->connection->fetchRow(
            $this->connection->select()->from(
                ['cpe' => $this->resource->getTableName('catalog_product_entity')],
                ['entity_id']
            )
            ->joinLeft(
                ['cpsl' => $this->resource->getTableName('catalog_product_super_link')],
                'cpe.entity_id = cpsl.product_id',
                'parent_id'
            )
            ->where('sku = ?', $sku)
        );

        $id = $row['parent_id'] > 0 ? $row['parent_id'] : $row['entity_id'];

        return (int)$id;
    }
}
