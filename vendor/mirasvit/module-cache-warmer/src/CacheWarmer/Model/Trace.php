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
use Mirasvit\CacheWarmer\Api\Data\TraceInterface;

class Trace extends AbstractModel implements TraceInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Trace::class);
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
    public function getEntityType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityType($value)
    {
        return $this->setData(self::ENTITY_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param int|string $value
     * @return AbstractModel|TraceInterface|Trace
     */
    public function setEntityId($value)
    {
        return $this->setData(self::ENTITY_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrace()
    {
        try {
            $data = \Zend_Json::decode($this->getData(self::TRACE));
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
        return $this->setData(self::TRACE, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt()
    {
        return $this->getData(self::STARTED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt($value)
    {
        return $this->setData(self::STARTED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFinishedAt()
    {
        return $this->getData(self::FINISHED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setFinishedAt($value)
    {
        return $this->setData(self::FINISHED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
}
