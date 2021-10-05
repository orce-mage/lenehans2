<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace MidoriWeb\Custom\Model\Data;

use MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DeliveryMessageContent extends AbstractSimpleObject implements DeliveryMessageContentInterface
{

    /**
     * @return string|null
     */
    public function getFromDay(): ?string
    {
        return $this->_get(self::FROM_DAY);
    }

    /**
     * @param string|null $fromDay
     */
    public function setFromDay(?string $fromDay): void
    {
        $this->setData(self::FROM_DAY, $fromDay);
    }

    /**
     * @return string|null
     */
    public function getToDay(): ?string
    {
        return $this->_get(self::TO_DAY);
    }

    /**
     * @param string|null $toDay
     */
    public function setToDay(?string $toDay): void
    {
        $this->setData(self::TO_DAY, $toDay);
    }

    /**
     * @return string|null
     */
    public function getToDate(): ?string
    {
        return $this->_get(self::TO_DATE);
    }

    /**
     * @param string|null $toDate
     */
    public function setToDate(?string $toDate): void
    {
        $this->setData(self::TO_DATE, $toDate);
    }
}
