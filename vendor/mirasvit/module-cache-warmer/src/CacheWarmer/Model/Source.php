<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */




namespace Mirasvit\CacheWarmer\Model;


use Magento\Framework\Model\AbstractModel;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;

class Source extends AbstractModel implements SourceInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Source::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceName()
    {
        return $this->getData(self::SOURCE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceName($value)
    {
        return $this->setData(self::SOURCE_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceType()
    {
        return $this->getData(self::SOURCE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceType($value)
    {
        return $this->setData(self::SOURCE_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function setPath($value)
    {
        return $this->setData(self::PATH, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastSyncronizedAt()
    {
        return $this->getData(self::LAST_SYNC_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastSyncronizedAt($value)
    {
        return $this->setData(self::LAST_SYNC_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        return explode(',', $this->getData(self::CUSTOMER_GROUPS));
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroups($value)
    {
        rsort($value);
        $groups = implode(',', $value);

        return $this->setData(self::CUSTOMER_GROUPS, $groups);
    }
}
