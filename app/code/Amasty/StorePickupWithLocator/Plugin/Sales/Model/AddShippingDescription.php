<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Plugin\Sales\Model;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Sales\Model\Order;

/**
 * Add store pickup information to shipping description
 * TODO: The implementation can be changed to introduction block,
 * for pdf you can look at \Magento\Sales\Model\Order\Pdf\AbstractPdf
 */
class AddShippingDescription
{
    /**
     * @var OrderRepositoryInterface
     */
    private $pickupOrderRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $pickupOrders = [];

    public function __construct(OrderRepositoryInterface $pickupOrderRepository, ConfigProvider $configProvider)
    {
        $this->pickupOrderRepository = $pickupOrderRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Order $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetShippingDescription(Order $subject, ?string $result): ?string
    {
        if (!$this->configProvider->isStorePickupEnabled()) {
            return $result;
        }

        $curbsideInfo = '';
        $curbsideLabel = '';
        $orderId = $subject->getId();
        if (empty($this->pickupOrders[$orderId])) {
            $this->pickupOrders[$orderId] = $this->pickupOrderRepository->getByOrderId($orderId);
        }

        $pickupOrder = $this->pickupOrders[$orderId];
        if ($pickupOrder->getIsCurbsidePickup()) {
            $curbsideLabel = ",\r\n" . $this->configProvider->getCurbsideCheckboxLabel();
        }

        if ($comment = $pickupOrder->getCurbsidePickupComment()) {
            $curbsideInfo = ",\r\n" . __('Pickup Details') . ":\r\n" . $comment;
        }

        return $result . $curbsideLabel . $curbsideInfo;
    }
}
