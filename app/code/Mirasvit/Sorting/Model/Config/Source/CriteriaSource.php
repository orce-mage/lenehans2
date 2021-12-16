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

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Model\ConfigProvider;
use Mirasvit\Sorting\Repository\CriterionRepository;
use Mirasvit\Sorting\Service\CriteriaManagementService;

class CriteriaSource implements ArrayInterface
{
    private $criterionRepository;

    private $config;

    private $criteriaManagement;

    public function __construct(
        CriteriaManagementService $criteriaManagement,
        CriterionRepository $criterionRepository,
        ConfigProvider $config
    ) {
        $this->criteriaManagement  = $criteriaManagement;
        $this->criterionRepository = $criterionRepository;
        $this->config              = $config;
    }

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }

    public function toArray(): array
    {
        $options = [];

        $collection = $this->criterionRepository->getCollection();
        $collection->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
            ->setOrder(CriterionInterface::POSITION, 'asc');

        foreach ($collection as $criterion) {
            $code           = $criterion->getCode();
            $options[$code] = $criterion->getName();
        }

        // if criteria not configured yet - use default sort by options
        if (count($options) === 0) {
            $options = $this->criteriaManagement->getDefaultSortByList();
        }

        return $options;
    }
}
