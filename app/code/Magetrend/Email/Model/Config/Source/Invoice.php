<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Config\Source;

class Invoice implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    public $invoiceRepository;

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
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->invoiceRepository = $invoiceRepository;
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
            '' => '--- Choose Invoice ---',
            'custom' => 'Custom Invoice ID'
        ];
        $sortOrder = $this->sortOrderBuilder->setField('created_at')
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(50)
            ->setCurrentPage(1)
            ->setSortOrders([$sortOrder])
            ->create();

        $invoices = $this->invoiceRepository->getList($searchCriteria);
        if ($invoices->getSize() == 0) {
            return $options;
        }

        foreach ($invoices->getItems() as $invoice) {
            $options[$invoice->getIncrementId()] = $invoice->getIncrementId();
        }

        return $options;
    }
}
