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
use Mirasvit\CacheWarmer\Api\Data\LogInterface;

class Log extends AbstractModel implements LogInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(LogInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseTime()
    {
        return $this->getData(LogInterface::RESPONSE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseTime($value)
    {
        return $this->setData(LogInterface::RESPONSE_TIME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->getData(LogInterface::IS_HIT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsHit($value)
    {
        return $this->setData(LogInterface::IS_HIT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->getData(LogInterface::URI);
    }

    /**
     * {@inheritdoc}
     */
    public function setUri($value)
    {
        return $this->setData(LogInterface::URI, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(LogInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(LogInterface::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\CacheWarmer\Model\ResourceModel\Log');
    }
}