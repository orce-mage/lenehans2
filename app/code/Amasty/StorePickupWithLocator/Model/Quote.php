<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Framework\Model\AbstractModel;

class Quote extends AbstractModel implements QuoteInterface
{
    protected function _construct()
    {
        $this->_init(QuoteResourceModel::class);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return int|null
     */
    public function getAddressId()
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * @param int|null $addressId
     * @return $this
     */
    public function setAddressId($addressId)
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * @return string|null
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * @return int|null
     */
    public function getTimeFrom()
    {
        return $this->getData(self::TIME_FROM);
    }

    /**
     * @param int $timeFrom
     * @return $this
     */
    public function setTimeFrom($timeFrom)
    {
        return $this->setData(self::TIME_FROM, $timeFrom);
    }

    /**
     * @return int|null
     */
    public function getTimeTo()
    {
        return $this->getData(self::TIME_TO);
    }

    /**
     * @param int $timeTo
     * @return $this
     */
    public function setTimeTo($timeTo)
    {
        return $this->setData(self::TIME_TO, $timeTo);
    }

    /**
     * @return bool
     */
    public function getIsCurbsidePickup()
    {
        return (bool)$this->getData(self::IS_CURBSIDE_PICKUP);
    }

    /**
     * @param bool $isCurbsidePickup
     * @return $this
     */
    public function setIsCurbsidePickup(bool $isCurbsidePickup)
    {
        return $this->setData(self::IS_CURBSIDE_PICKUP, $isCurbsidePickup);
    }

    /**
     * @return string
     */
    public function getCurbsidePickupComment()
    {
        return (string)$this->getData(self::CURBSIDE_PICKUP_COMMENT);
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setCurbsidePickupComment(string $comment)
    {
        return $this->setData(self::CURBSIDE_PICKUP_COMMENT, $comment);
    }
}
