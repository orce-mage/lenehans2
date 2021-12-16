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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Service;

use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;

/** @SuppressWarnings(PHPMD) */
class CriteriaManagementService
{
    const DEFAULT_DIRECTION = 'asc';

    private $config;

    private $catalogConfig;

    private $request;

    private $registry;

    private $criterionRepository;

    public function __construct(
        CriterionRepository $criterionRepository,
        RequestInterface $request,
        CatalogConfig $catalogConfig,
        Registry $registry,
        ConfigProvider $config
    ) {
        $this->criterionRepository = $criterionRepository;
        $this->request             = $request;
        $this->catalogConfig       = $catalogConfig;
        $this->registry            = $registry;
        $this->config              = $config;
    }

    public function getCurrentCriterion(?string $criterionCode): ?CriterionInterface
    {
        $criterion = null;

        // 1. load criterion from order set as a GET parameter
        if ($criterionCode) {
            $criterion = $this->criterionRepository->getByCode($criterionCode);
        }

        // 2. if not exists - load default criterion otherwise
        if (!$criterion &&
            ($this->request->getModuleName() === 'catalogsearch'
                || $this->request->getModuleName() === 'searchautocomplete')) {
            return null;
        }

        if (!$criterion) {
            $criterion = $this->getDefaultCriterion();
        }

        // 3. load first criterion
        if (!$criterion) {
            $collection = $this->criterionRepository->getCollection()
                ->addFieldToFilter(CriterionInterface::IS_ACTIVE, true);

            $criterion = $collection->getFirstItem();

            if (!$criterion->getId()) {
                $criterion = null;
            }
        }

        return $criterion;
    }

    public function getDefaultCriterion(): ?CriterionInterface
    {
        $collection = $this->criterionRepository->getCollection()
            ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1);

        if ($this->request->getModuleName() === 'catalogsearch'
            || $this->request->getModuleName() === 'searchautocomplete') {
            $collection->addFieldToFilter(CriterionInterface::IS_SEARCH_DEFAULT, 1);
        } else {
            $collection->addFieldToFilter(CriterionInterface::IS_DEFAULT, 1);
        }

        /** @var CriterionInterface $criterion */
        $criterion = $collection->getFirstItem();

        if ($this->registry->registry('current_category')) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->registry->registry('current_category');

            //we have custom sort for catalog
            //we use getData - to get the value of this exact category
            if ($this->request->getModuleName() != 'brand' && $category->getData('default_sort_by')
                && $category->getData('default_sort_by') !== $this->catalogConfig->getProductListDefaultSortBy()) {
                $customCriterion = $this->criterionRepository->getCollection()
                    ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
                    ->addFieldToFilter(CriterionInterface::CODE, $category->getDefaultSortBy())
                    ->getFirstItem();

                if ($customCriterion->getId()) {
                    $criterion = $customCriterion;
                }
            }
        }

        if (!$criterion->getId()) {
            $systemSortBy = $this->catalogConfig->getProductListDefaultSortBy();

            $customCriterion = $this->criterionRepository->getCollection()
                ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
                ->addFieldToFilter(CriterionInterface::CODE, $systemSortBy)
                ->getFirstItem();

            if ($customCriterion->getId()) {
                $criterion = $customCriterion;
            }
        }

        return $criterion->getId() ? $criterion : null;
    }

    public function getDefaultDirection(CriterionInterface $criterion): string
    {
        foreach ($criterion->getConditionCluster()->getFrames() as $frame) {
            foreach ($frame->getNodes() as $node) {
                return $node->getDirection();
            }
        }

        return 'asc';
    }

    public function getDefaultSortByList(): array
    {
        $options = ['position' => __('Position')];

        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }
}
