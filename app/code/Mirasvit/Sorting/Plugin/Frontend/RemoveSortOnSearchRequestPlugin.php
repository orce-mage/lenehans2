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

use Magento\Eav\Model\AttributeRepository;
use Mirasvit\Sorting\Repository\CriterionRepository;

/**
 * @see \Magento\Framework\Search\Request::getSort()
 */
class RemoveSortOnSearchRequestPlugin
{
    private $criterionRepository;

    private $attributeRepository;

    public function __construct(
        CriterionRepository $criterionRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->criterionRepository = $criterionRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @SuppressWarnings(PHPMD)
     * @param mixed $subject
     * @param array $orders
     *
     * @return array
     */
    public function afterGetSort($subject, $orders)
    {
        if (!$orders || !is_array($orders)) {
            return $orders;
        }

        $allowed = [];
        foreach ($this->criterionRepository->getCollection() as $criterion) {
            $allowed[] = $criterion->getCode();
        }

        $result = [];

        foreach ($orders as $order) {
            if (!isset($order['field'])) {
                continue;
            }

            $field = $order['field'];

            if (is_numeric($field)) { // field = '1'
                continue;
            }

            try {
                $attribute = $this->attributeRepository->get('catalog_product', $field);
            } catch (\Exception $e) {
                $attribute = null;
            }

            if ($attribute || $order['field'] == 'position' || !in_array($order['field'], $allowed)) {
                $result[] = $order;
            }
        }

        return $result;
    }
}
