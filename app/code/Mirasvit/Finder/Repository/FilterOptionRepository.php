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

namespace Mirasvit\Finder\Repository;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Filter\FilterManager;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterfaceFactory;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Model\ResourceModel\FilterOption\Collection;
use Mirasvit\Finder\Model\ResourceModel\FilterOption\CollectionFactory;

class FilterOptionRepository
{
    private $entityManager;

    private $collectionFactory;

    private $factory;

    private $filterManager;

    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        FilterOptionInterfaceFactory $factory,
        FilterManager $filterManager
    ) {
        $this->entityManager     = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->factory           = $factory;
        $this->filterManager     = $filterManager;
    }

    /**
     * @return FilterOptionInterface[]|Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    public function create(): FilterOptionInterface
    {
        return $this->factory->create();
    }

    public function get(int $id): ?FilterOptionInterface
    {
        $model = $this->create();
        $model = $this->entityManager->load($model, $id);

        return $model->getId() ? $model : null;
    }

    public function save(FilterOptionInterface $model): FilterOptionInterface
    {
        if (!$model->getUrlKey()) {
            $urlKey = $this->filterManager->translitUrl($model->getName());
            $urlKey = str_replace(ConfigProvider::DELIMITER_MINUS, '_', $urlKey);
            $urlKey = str_replace(ConfigProvider::DELIMITER_SLASH, '_', $urlKey);
            $model->setUrlKey($urlKey);
        }

        return $this->entityManager->save($model);
    }

    public function delete(FilterOptionInterface $model): void
    {
        $this->entityManager->delete($model);
    }
}
