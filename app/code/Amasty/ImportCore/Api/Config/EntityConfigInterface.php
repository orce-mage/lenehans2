<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config;

interface EntityConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getEntityCode();

    /**
     * @param string $entityCode
     *
     * @return void
     */
    public function setEntityCode($entityCode);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @param string $group
     *
     * @return void
     */
    public function setGroup($group);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description);

    /**
     * @return bool
     */
    public function isHiddenInLists();

    /**
     * @param bool $isHiddenInLists
     *
     * @return void
     */
    public function setHiddenInLists($isHiddenInLists);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\BehaviorInterface[]
     */
    public function getBehaviors();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\BehaviorInterface[] $behaviors
     *
     * @return void
     */
    public function setBehaviors($behaviors);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\IndexerConfigInterface|null
     */
    public function getIndexerConfig();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\IndexerConfigInterface $indexerConfig
     *
     * @return void
     */
    public function setIndexerConfig($indexerConfig);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\FileUploaderConfigInterface|null
     */
    public function getFileUploaderConfig();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\FileUploaderConfigInterface $fileUploaderConfig
     *
     * @return void
     */
    public function setFileUploaderConfig($fileUploaderConfig);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function getFieldsConfig();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface $fieldsConfig
     *
     * @return void
     */
    public function setFieldsConfig($fieldsConfig);

    /**
     * @return \Amasty\ImportCore\Api\Config\EntityConfigExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Amasty\ImportCore\Api\Config\EntityConfigExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\EntityConfigExtensionInterface $extensionAttributes
    );
}
