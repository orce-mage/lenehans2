<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model\Sales;

use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class AddressResolver
{
    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var OrderRepositoryInterface
     */
    private $magentoOrderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        LocationFactory $locationFactory,
        LocationResource $locationResource,
        OrderRepositoryInterface $magentoOrderRepository,
        CartRepositoryInterface $quoteRepository,
        ConfigProvider $configProvider
    ) {
        $this->locationFactory = $locationFactory;
        $this->locationResource = $locationResource;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Order|CartInterface $entity
     * @param int $locationId
     */
    public function setShippingInformation($entity, $locationId)
    {
        /** @var \Amasty\Storelocator\Model\Location $location */
        $location = $this->locationFactory->create();
        $this->locationResource->load($location, $locationId);

        $carrierTitle = $this->configProvider->getCarrierTitle() ?: 'Store Pickup';
        $entity->getShippingAddress()
            ->setFirstname(__($carrierTitle . ':'))
            ->setLastname($location->getName())
            ->setCountryId($location->getCountry())
            ->setRegion($location->getStateName())
            ->setRegionId($location->getState())
            ->setStreet($location->getAddress())
            ->setCity($location->getCity())
            ->setPostcode($location->getZip())
            ->setTelephone($location->getPhone());

        if ($entity instanceof Order) {
            $entity->setShippingDescription(__($carrierTitle . ' - ' . $location->getName()));
            $this->magentoOrderRepository->save($entity);
        } elseif ($entity instanceof CartInterface) {
            $this->quoteRepository->save($entity);
        }
    }
}
