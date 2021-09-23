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
use Mirasvit\CacheWarmer\Api\Data\JobInterface;

class Job extends AbstractModel implements JobInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(JobInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->getData(JobInterface::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($value)
    {
        return $this->setData(JobInterface::PRIORITY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(JobInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(JobInterface::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter()
    {
        try {
            $data = \Zend_Json::decode($this->getData(JobInterface::FILTER_SERIALIZED));
            if (!$data) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilter($value)
    {
        return $this->setData(JobInterface::FILTER_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        try {
            $data = \Zend_Json::decode($this->getData(JobInterface::INFO_SERIALIZED));
            if (!$data) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setInfo($value)
    {
        return $this->setData(JobInterface::INFO_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace()
    {
        try {
            $data = \Zend_Json::decode($this->getData(JobInterface::TRACE_SERIALIZED));
            if (!$data) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setTrace($value)
    {
        return $this->setData(JobInterface::TRACE_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt()
    {
        return $this->getData(JobInterface::STARTED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt($value)
    {
        return $this->setData(JobInterface::STARTED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFinishedAt()
    {
        return $this->getData(JobInterface::FINISHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setFinishedAt($value)
    {
        return $this->setData(JobInterface::FINISHED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(JobInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(JobInterface::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(JobInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(JobInterface::UPDATED_AT, $value);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Job::class);
    }
}
