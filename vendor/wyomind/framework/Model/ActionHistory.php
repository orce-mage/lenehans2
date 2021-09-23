<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model;

class ActionHistory extends \Magento\Framework\Model\AbstractModel implements \Wyomind\Framework\Api\Data\ActionHistoryInterface
{
    /**
     * @var string
     */
    private $module;

    /**
     * @var string|null
     */
    private $entity;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param string $module
     * @param string $entity
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $module = 'Framework',
        $entity = null,
        array $data = []
    ) {
    
        $this->module = $module;
        $this->entity = $entity;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('Wyomind\\' . $this->module . '\Model\ResourceModel' . ($this->entity ? '\\' . $this->entity : '') . '\ActionHistory');
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * {@inheritDoc}
     */
    public function getVersionId()
    {
        return $this->_getData(self::VERSION_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setVersionId($versionId)
    {
        return $this->setData(self::VERSION_ID, $versionId);
    }

    /**
     * {@inheritDoc}
     */
    public function getActionType()
    {
        return $this->_getData(self::ACTION_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function setActionType($actionType)
    {
        return $this->setData(self::ACTION_TYPE, $actionType);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrigin()
    {
        return $this->_getData(self::ORIGIN);
    }

    /**
     * {@inheritDoc}
     */
    public function setOrigin($origin)
    {
        return $this->setData(self::ORIGIN, $origin);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->_getData(self::USERNAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername($username)
    {
        return $this->setData(self::USERNAME, $username);
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return $this->_getData(self::RESULT);
    }

    /**
     * {@inheritDoc}
     */
    public function setResult($result)
    {
        return $this->setData(self::RESULT, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage()
    {
        return $this->_getData(self::MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getDetails()
    {
        return $this->_getData(self::DETAILS);
    }

    /**
     * {@inheritDoc}
     */
    public function setDetails($details)
    {
        return $this->setData(self::DETAILS, $details);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
