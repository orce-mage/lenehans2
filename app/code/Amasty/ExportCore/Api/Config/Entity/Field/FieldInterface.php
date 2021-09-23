<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Entity\Field;

interface FieldInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setName(?string $name): FieldInterface;

    /**
     * @return string
     */
    public function getLabel(): ?string;

    /**
     * @param string $label
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setLabel(?string $label): FieldInterface;

    /**
     * @return string
     */
    public function getMap(): ?string;

    /**
     * @param string $map
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setMap(?string $map): FieldInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\ActionInterface[]
     */
    public function getActions(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\ActionInterface[] $actions
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setActions(?array $actions): FieldInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface
     */
    public function getFilter();

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface $filter
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setFilter($filter): FieldInterface;

    /**
     * @param bool $remove
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setRemove($remove);

    /**
     * @return bool
     */
    public function getRemove();

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface
     */
    public function getExtensionAttributes(): ?\Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
    ): FieldInterface;
}
