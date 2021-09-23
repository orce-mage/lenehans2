<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;
use Magento\Framework\DataObject;

class Field extends DataObject implements FieldInterface
{
    const NAME = 'name';
    const MAP = 'map';
    const IS_FILE = 'is_file';
    const IS_IDENTITY = 'is_identity';
    const IDENTIFICATION = 'identification';
    const ACTIONS = 'actions';
    const VALIDATIONS = 'validations';
    const PRESELECTED = 'preselected';
    const REMOVE = 'remove';
    const SYNCHRONIZATION = 'synchronization';
    const EXTENSION_ATTRIBUTES = 'extension_attributes';

    /**
     * @var FieldExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        FieldExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getMap()
    {
        return $this->getData(self::MAP);
    }

    /**
     * @inheritDoc
     */
    public function setMap($map)
    {
        return $this->setData(self::MAP, $map);
    }

    /**
     * @inheritDoc
     */
    public function isFile()
    {
        return $this->getData(self::IS_FILE);
    }

    /**
     * @inheritDoc
     */
    public function setIsFile($isFile)
    {
        $this->setData(self::IS_FILE, $isFile);
    }

    /**
     * @inheritDoc
     */
    public function isIdentity()
    {
        return (bool)$this->getData(self::IS_IDENTITY);
    }

    /**
     * @inheritDoc
     */
    public function setIsIdentity($isIdentity)
    {
        $this->setData(self::IS_IDENTITY, $isIdentity);
    }

    /**
     * @inheritDoc
     */
    public function getIdentification()
    {
        return $this->getData(self::IDENTIFICATION);
    }

    /**
     * @inheritDoc
     */
    public function setIdentification($identification)
    {
        $this->setData(self::IDENTIFICATION, $identification);
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return $this->getData(self::ACTIONS);
    }

    /**
     * @inheritDoc
     */
    public function setActions($actions)
    {
        $this->setData(self::ACTIONS, $actions);
    }

    /**
     * @inheritDoc
     */
    public function getValidations()
    {
        return $this->getData(self::VALIDATIONS);
    }

    /**
     * @inheritDoc
     */
    public function setValidations($validations)
    {
        $this->setData(self::VALIDATIONS, $validations);
    }

    /**
     * @inheritDoc
     */
    public function setRemove($remove)
    {
        $this->setData(self::REMOVE, $remove);
    }

    /**
     * @inheritDoc
     */
    public function getRemove()
    {
        return $this->getData(self::REMOVE) ?: false;
    }

    /**
     * @inheritDoc
     */
    public function setPreselected(PreselectedInterface $preselected)
    {
        $this->setData(self::PRESELECTED, $preselected);
    }

    /**
     * @inheritDoc
     */
    public function getPreselected()
    {
        return $this->getData(self::PRESELECTED);
    }

    /**
     * @inheritDoc
     */
    public function setSynchronization($synchronizationData)
    {
        $this->setData(self::SYNCHRONIZATION, $synchronizationData);
    }

    /**
     * @inheritDoc
     */
    public function getSynchronization()
    {
        return $this->getData(self::SYNCHRONIZATION);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES);
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
    ) {
        $this->setData(self::EXTENSION_ATTRIBUTES, $extensionAttributes);
    }
}
