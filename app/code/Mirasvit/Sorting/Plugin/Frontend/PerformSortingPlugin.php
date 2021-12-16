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

namespace Mirasvit\Sorting\Plugin\Frontend;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Service\CriteriaApplierService;

/**
 * Apply sorting using MySQL
 * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::load()
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface::apply()
 */
class PerformSortingPlugin
{
    private $criteriaApplierService;

    private $config;

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(
        CriteriaApplierService $criteriaApplierService,
        ConfigProvider $config
    ) {
        $this->criteriaApplierService = $criteriaApplierService;
        $this->config                 = $config;
    }

    /**
     * @param Collection $subject
     * @param bool       $print
     * @param bool       $log
     *
     * @return array
     */
    public function beforeLoad($subject, $print = false, $log = false)
    {
        if (!$this->config->isApplicable()) {
            return [$print, $log];
        }

        if (!$subject->isLoaded()) {
            $this->criteriaApplierService->sortCollection($subject);

            $this->collection = $subject;
        }

        return [$print, $log];
    }

    /**
     * @param mixed $subject
     * @param mixed $result
     *
     * @return mixed
     */
    public function afterApply($subject, $result)
    {
        if (!$this->config->isApplicable()) {
            return $result;
        }

        if ($this->collection && !$this->collection->isLoaded()) {
            $this->criteriaApplierService->sortCollection($this->collection);
        }

        return $result;
    }

    /**
     * Remove order by entity_id
     *
     * @param object $subject
     * @param string $field
     * @param string $direction
     *
     * @return array
     */
    public function beforeSetOrder($subject, $field, $direction = '')
    {
        if (!$this->config->isApplicable()) {
            return [$field, $direction];
        }

        if ($field === 'entity_id') {
            $field = '1';
        }

        return [$field, $direction];
    }

}
