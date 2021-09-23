<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity;

use Amasty\ImportCore\Api\Config\Entity\FieldsConfigExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;
use Magento\Framework\DataObject;

class FieldsConfig extends DataObject implements FieldsConfigInterface
{
    const FIELDS = 'fields';
    const ROW_ACTION_CLASS = 'row_action_class';
    const ROW_VALIDATION_CLASS = 'row_validation_class';
    const SAMPLE_DATA = 'sample_data';
    const EXTENSION_ATTRIBUTES = 'extension_attributes';

    /**
     * @var FieldsConfigExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        FieldsConfigExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    public function getFields()
    {
        return $this->getData(self::FIELDS);
    }

    /**
     * @inheritDoc
     */
    public function setFields($fields)
    {
        $this->setData(self::FIELDS, $fields);
    }

    /**
     * @inheritDoc
     */
    public function getRowActionClass()
    {
        return $this->getData(self::ROW_ACTION_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setRowActionClass($class)
    {
        $this->setData(self::ROW_ACTION_CLASS, $class);
    }

    /**
     * @inheritDoc
     */
    public function getRowValidation()
    {
        return $this->getData(self::ROW_VALIDATION_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setRowValidation($class)
    {
        $this->setData(self::ROW_VALIDATION_CLASS, $class);
    }

    /**
     * @inheritDoc
     */
    public function getSampleData()
    {
        return $this->getData(self::SAMPLE_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setSampleData($sampleData)
    {
        $this->setData(self::SAMPLE_DATA, $sampleData);
    }

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\FieldsConfigExtensionInterface
     */
    public function getExtensionAttributes()
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES);
    }

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\FieldsConfigExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Entity\FieldsConfigExtensionInterface $extensionAttributes
    ) {
        $this->setData(self::EXTENSION_ATTRIBUTES, $extensionAttributes);
    }
}
