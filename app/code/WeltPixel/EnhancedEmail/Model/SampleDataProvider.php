<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Model;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Class SampleDataProvider
 * @package WeltPixel\EnhancedEmail\Model
 */
class SampleDataProvider
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $_creditmemoRepository;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $_shipmentRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;
    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;


    /**
     * SampleDataProvider constructor.
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRegistry $customerRegistry
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        CustomerViewHelper $customerViewHelper

    )
    {
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_orderRepository = $orderRepository;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_creditmemoRepository = $creditmemoRepository;
        $this->_shipmentRepository = $shipmentRepository;
        $this->_customerRepository = $customerRepository;
        $this->_subscriber = $subscriber;
        $this->_storeManager = $storeManager;
        $this->customerRegistry = $customerRegistry;
        $this->dataProcessor = $dataProcessor;
        $this->customerViewHelper = $customerViewHelper;

    }

    /**
     * @return string
     */
    public function fetchOrder()
    {
        $orderData = '';
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_searchCriteriaBuilder->addFilter(
            OrderInterface::STORE_ID,
            $storeId,
            'eq'
        )->setPageSize(1);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_orderRepository->getList($searchCriteria);

        if ($searchResults->getSize() > 0) {
            $orderData = $searchResults->getFirstItem();
        }

        return $orderData;

    }

    /**
     * @return string
     */
    public function fetchInvoice()
    {
        $invoiceData = '';
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_searchCriteriaBuilder->addFilter(
            InvoiceInterface::STORE_ID,
            $storeId,
            'eq'
        )->setPageSize(1);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_invoiceRepository->getList($searchCriteria);

        if ($searchResults->getSize() > 0) {
            $invoiceData = $searchResults->getFirstItem();
        }

        return $invoiceData;
    }

    /**
     * @return string
     */
    public function fetchCreditmemo()
    {
        $creditmemoData = '';
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_searchCriteriaBuilder->addFilter(
            CreditmemoInterface::STORE_ID,
            $storeId,
            'eq'
        )->setPageSize(1);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_creditmemoRepository->getList($searchCriteria);

        if ($searchResults->getSize() > 0) {
            $creditmemoData = $searchResults->getFirstItem();
        }

        return $creditmemoData;
    }

    /**
     * @return string
     */
    public function fetchShipment()
    {
        $shipmentData = '';
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_searchCriteriaBuilder->addFilter(
            ShipmentInterface::STORE_ID,
            $storeId,
            'eq'
        );
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_shipmentRepository->getList($searchCriteria);

        if ($searchResults->getSize() > 0) {
            $shipmentData = $searchResults->getFirstItem();
        }

        return $shipmentData;
    }

    /**
     * @return CustomerInterface|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchCustomer()
    {
        $customer = false;
        $storeId = $this->_storeManager->getStore()->getId();
        $filters = [
            $this->_filterBuilder->setField(CustomerInterface::STORE_ID)->setValue($storeId)->create()
        ];
        $this->_searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_customerRepository->getList($searchCriteria);

        if ($searchResults->getTotalCount() > 0) {
            $customer = $searchResults->getItems()[0];
            if($customer->getEmail()) {
                $customer = $this->_getCustomerObjectForEmail($customer);
            }
        }

        return $customer;

    }

    /**
     * @param $customer
     * @return \Magento\Customer\Model\Data\CustomerSecure
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getCustomerObjectForEmail($customer) {
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * @return $this|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchSubscriber()
    {
        $subscriber = false;
        $customer = $this->fetchCustomer();
        if ($customer) {
            $subscriber = $this->_subscriber->loadByCustomerId($customer->getId());
        }

        return $subscriber;
    }

    /**
     * @return string
     */
    public function fetchWishlist()
    {
        return '';

    }

}