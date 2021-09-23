<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Amasty\Storelocator\Api\Data\ReviewInterface;

/**
 * Class Review
 */
class Review extends AbstractModel implements ReviewInterface, IdentityInterface
{
    const RATING_DIVIDER = 20;

    const CACHE_TAG = 'amlocator_reviews';

    public function _construct()
    {
        $this->_init(ResourceModel\Review::class);
    }

    /**
     * @inheritdoc
     */
    public function getLocationId()
    {
        return $this->_getData(ReviewInterface::LOCATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setLocationId($id)
    {
        $this->setData(ReviewInterface::LOCATION_ID, $id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(ReviewInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($id)
    {
        $this->setData(ReviewInterface::CUSTOMER_ID, $id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRating()
    {
        return $this->_getData(ReviewInterface::RATING);
    }

    /**
     * @inheritdoc
     */
    public function setRating($rating)
    {
        $this->setData(ReviewInterface::RATING, $rating);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReviewText()
    {
        return $this->_getData(ReviewInterface::REVIEW_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setReviewText($text)
    {
        $this->setData(ReviewInterface::REVIEW_TEXT, $text);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPlacedAt()
    {
        return $this->_getData(ReviewInterface::PLACED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setPlacedAt($date)
    {
        $this->setData(ReviewInterface::PLACED_AT, $date);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPublishedAt()
    {
        return $this->_getData(ReviewInterface::PUBLISHED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setPublishedAt($date)
    {
        $this->setData(ReviewInterface::PUBLISHED_AT, $date);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(ReviewInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(ReviewInterface::STATUS, $status);

        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }
}
