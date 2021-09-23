<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\Location;

use Magento\Framework\Session\SessionManagerInterface as CheckoutSession;

class LocationsAvailability
{
    const NO_AVAILABLE_LOCATIONS = 'amasty_storepickup_no_locations';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->checkoutSession->setStepData('checkout', self::NO_AVAILABLE_LOCATIONS, !$isAvailable);
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        // @TODO: temporary we show method always
        return true;
//        return (bool)!$this->checkoutSession->getStepData('checkout', self::NO_AVAILABLE_LOCATIONS);
    }
}
