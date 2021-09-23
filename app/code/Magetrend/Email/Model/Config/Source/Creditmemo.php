<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Config\Source;

class Creditmemo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    public $creditmemoRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    public $sortOrderBuilder;

    /**
     * Order constructor.
     *
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $key => $value) {
            $options[] = ['value' => $key, 'label'=> $value];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [
            '' => '--- Choose Creditmemo ---',
            'custom' => 'Custom Creditmemo ID'
        ];
        $sortOrder = $this->sortOrderBuilder->setField('created_at')
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(50)
            ->setCurrentPage(1)
            ->setSortOrders([$sortOrder])
            ->create();

        $creditmemos = $this->creditmemoRepository->getList($searchCriteria);
        if ($creditmemos->getSize() == 0) {
            return $options;
        }

        foreach ($creditmemos->getItems() as $creditmemo) {
            $options[$creditmemo->getIncrementId()] = $creditmemo->getIncrementId();
        }

        return $options;
    }
}
