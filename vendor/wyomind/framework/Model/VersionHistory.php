<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model;

class VersionHistory extends \Magento\Framework\Model\AbstractModel implements \Wyomind\Framework\Api\Data\VersionHistoryInterface
{
    /**
     * @var string|null
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
        $this->_init('Wyomind\\' . $this->module . '\Model\ResourceModel' . ($this->entity ? '\\' . $this->entity : '') . '\VersionHistory');
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
    public function getContent()
    {
        return $this->_getData(self::CONTENT);
    }

    /**
     * {@inheritDoc}
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
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
