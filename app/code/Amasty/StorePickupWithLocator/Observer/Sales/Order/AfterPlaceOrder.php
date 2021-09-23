<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Observer\Sales\Order;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Api\Data\OrderInterfaceFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\OrderRepository;
use Amasty\StorePickupWithLocator\Model\Quote;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Amasty\StorePickupWithLocator\Model\Sales\AddressResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterPlaceOrder for move date information from quote to order, 'sales_model_service_quote_submit_success' event
 */
class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressResolver
     */
    private $orderAddressResolver;

    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        OrderInterfaceFactory $orderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressResolver $orderAddressResolver
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderAddressResolver = $orderAddressResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return $this;
        }

        if ($order->getShippingMethod() !== Shipping::SHIPPING_NAME) {
            return $this;
        }

        $this->searchCriteriaBuilder->addFilter(QuoteInterface::QUOTE_ID, $order->getQuoteId());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $quoteList = $this->quoteRepository->getList($searchCriteria);

        /** @var Quote $quote */
        foreach ($quoteList->getItems() as $quote) {
            /** @var OrderInterface $orderEntity */
            $orderEntity = $this->orderFactory->create();
            $locationId = $quote->getStoreId();

            $data = [
                OrderInterface::ORDER_ID => $order->getId(),
                OrderInterface::STORE_ID => $quote->getStoreId(),
                OrderInterface::DATE => $quote->getDate(),
                OrderInterface::TIME_FROM => $quote->getTimeFrom(),
                OrderInterface::TIME_TO => $quote->getTimeTo(),
                OrderInterface::IS_CURBSIDE_PICKUP => $quote->getIsCurbsidePickup(),
                OrderInterface::CURBSIDE_PICKUP_COMMENT => $quote->getCurbsidePickupComment()
            ];

            $this->orderRepository->save($orderEntity->setData($data));
            $this->orderAddressResolver->setShippingInformation($order, $locationId);
        }

        return $this;
    }
}
