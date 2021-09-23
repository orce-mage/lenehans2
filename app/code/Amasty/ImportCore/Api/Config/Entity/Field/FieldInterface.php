<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\IdentificationInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface;

interface FieldInterface
{
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
    public function getMap();

    /**
     * @param string $map
     *
     * @return void
     */
    public function setMap($map);

    /**
     * @return bool
     */
    public function isFile();

    /**
     * @param bool $isFile
     *
     * @return void
     */
    public function setIsFile($isFile);

    /**
     * @return bool
     */
    public function isIdentity();

    /**
     * @param bool $isIdentity
     *
     * @return void
     */
    public function setIsIdentity($isIdentity);

    /**
     * @return IdentificationInterface|null
     */
    public function getIdentification();

    /**
     * @param IdentificationInterface|null $identification
     *
     * @return void
     */
    public function setIdentification(?IdentificationInterface $identification);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface[]
     */
    public function getActions();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface[] $actions
     *
     * @return void
     */
    public function setActions($actions);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\ValidationInterface[]
     */
    public function getValidations();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\Field\ValidationInterface[] $validations
     *
     * @return void
     */
    public function setValidations($validations);

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface $preselected
     * @return void
     */
    public function setPreselected(PreselectedInterface $preselected);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface|null
     */
    public function getPreselected();

    /**
     * @param bool $remove
     * @return $this
     */
    public function setRemove($remove);

    /**
     * @return bool
     */
    public function getRemove();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface[] $synchronizationData
     * @return void
     */
    public function setSynchronization($synchronizationData);

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface[]
     */
    public function getSynchronization();

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\FieldExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Amasty\ImportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
    );
}
