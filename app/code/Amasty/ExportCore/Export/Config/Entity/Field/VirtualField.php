<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Entity\Field;

use Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface;
use Magento\Framework\DataObject;

class VirtualField extends DataObject implements VirtualFieldInterface
{
    const NAME = 'name';
    const LABEL = 'label';
    const GENERATOR = 'generator';

    /**
     * @var VirtualFieldExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        VirtualFieldExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    public function setName(?string $name): VirtualFieldInterface
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->getData(self::LABEL);
    }

    public function setLabel(?string $label): VirtualFieldInterface
    {
        $this->setData(self::LABEL, $label);

        return $this;
    }

    public function getGenerator(): ?\Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface
    {
        return $this->getData(self::GENERATOR);
    }

    public function setGenerator(
        \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $generatorClass
    ): VirtualFieldInterface {
        $this->setData(self::GENERATOR, $generatorClass);

        return $this;
    }

    public function getExtensionAttributes(): ?\Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface $extensionAttributes
    ): VirtualFieldInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
