<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Config\Source;

class Shipment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    public $shipmentRepository;

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
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->shipmentRepository = $shipmentRepository;
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
            '' => '--- Choose Shipment ---',
            'custom' => 'Custom Shipment ID'
        ];
        $sortOrder = $this->sortOrderBuilder->setField('created_at')
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(50)
            ->setCurrentPage(1)
            ->setSortOrders([$sortOrder])
            ->create();

        $shipments = $this->shipmentRepository->getList($searchCriteria);
        if ($shipments->getSize() == 0) {
            return $options;
        }

        foreach ($shipments->getItems() as $shipment) {
            $options[$shipment->getIncrementId()] = $shipment->getIncrementId();
        }

        return $options;
    }
}
