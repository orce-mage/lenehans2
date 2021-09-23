<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\View;

use Amasty\Storelocator\Model\Review as reviewModel;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Customer\Model\Session;
use Amasty\Storelocator\Model\ConfigProvider;

/**
 * Class Reviews
 */
class Reviews extends Template implements IdentityInterface
{
    protected $_template = 'Amasty_Storelocator::pages/reviews.phtml';

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Session $customerSession,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isCustomerAuthorized()
    {
        if ($this->customerSession->getCustomerId() === null) {
            $this->customerSession->start();
        }

        return (bool)$this->customerSession->getCustomerId();
    }

    /**
     * @return bool
     */
    public function isReviewsEnabled()
    {
        return $this->configProvider->isReviewsEnabled();
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return (int)$this->getData('location')->getId();
    }

    public function getLocationName()
    {
        return $this->getData('location')->getName();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [reviewModel::CACHE_TAG];
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return parent::getCacheKeyInfo() + ['l_id' => $this->getLocationId()];
    }
}
