<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Observer\Admin\Order;

use Amasty\StorePickupWithLocator\Model\OrderRepository;
use Amasty\StorePickupWithLocator\Model\Sales\AddressResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Save implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $magentoOrderRepository;

    /**
     * @var AddressResolver
     */
    private $orderAddressResolver;

    public function __construct(
        RequestInterface $request,
        OrderRepository $orderRepository,
        OrderRepositoryInterface $magentoOrderRepository,
        AddressResolver $orderAddressResolver
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->orderAddressResolver = $orderAddressResolver;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        $data = $this->request->getParam('ampickup');
        if (is_array($data) && !empty($data)) {
            if (!empty($data['location_id'])) {
                if (!empty($data['tinterval_id'])) {
                    list($timeFrom, $timeTo) = explode('|', $data['tinterval_id']);
                    $data['time_from'] = $timeFrom;
                    $data['time_to'] = $timeTo;
                }
                $data['order_id'] = $order->getId();
                $this->orderRepository->setAndSaveOrderData($data);
                $this->orderAddressResolver->setShippingInformation($order, $data['location_id']);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'If you select Store Pickup With Locator shipping method, 
                        please insert data to additional fields for save.'
                    )
                );
            }
        }
    }
}
