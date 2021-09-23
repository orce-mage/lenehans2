<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling;

use Amasty\ExportCore\Api\Config\Entity\Field\ActionInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\FieldModifier\EavOptionValue2OptionLabel;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;

class ActionConfigBuilder
{
    const IS_MULTISELECT = 'isMultiselect';
    const IS_PRESELECTED = 'preselected';
    const EAV_ENTITY_TYPE_CODE = 'eavEntityType';
    const OPTION_SOURCE = 'optionSource';

    /**
     * @var ActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var bool
     */
    private $isMultiselect;

    /**
     * @var bool
     */
    private $isPreselected;

    /**
     * @var string
     */
    private $eavEntityTypeCode;

    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPrepare;

    public function __construct(
        ActionInterfaceFactory $actionFactory,
        ConfigClassInterfaceFactory $configClassFactory,
        ArgumentsPrepare $argumentsPrepare
    ) {
        $this->actionFactory = $actionFactory;
        $this->configClassFactory = $configClassFactory;
        $this->argumentsPrepare = $argumentsPrepare;
    }

    /**
     * Set multiselect attribute type
     *
     * @param bool $isMultiselect
     *
     * @return $this
     */
    public function setIsMultiselect(bool $isMultiselect): ActionConfigBuilder
    {
        $this->isMultiselect = $this->argumentsPrepare->execute(
            [
                self::IS_MULTISELECT => [
                    'name'     => self::IS_MULTISELECT,
                    'xsi:type' => 'boolean',
                    'value'    => $isMultiselect
                ]
            ]
        );

        return $this;
    }

    /**
     * Set Eav Entity Type Code
     *
     * @param string $eavEntityTypeCode
     *
     * @return $this
     */
    public function setEavEntityTypeCode(string $eavEntityTypeCode): ActionConfigBuilder
    {
        $this->eavEntityTypeCode = $this->argumentsPrepare->execute(
            [
                self::EAV_ENTITY_TYPE_CODE => [
                    'name'     => self::EAV_ENTITY_TYPE_CODE,
                    'xsi:type' => 'string',
                    'value'    => $eavEntityTypeCode
                ]
            ]
        );

        return $this;
    }

    /**
     * Set preselected
     *
     * @param bool $preselected
     *
     * @return $this
     */
    public function setPreselected(bool $preselected): ActionConfigBuilder
    {
        $this->isPreselected = $this->argumentsPrepare->execute(
            [
                self::IS_PRESELECTED => [
                    'name'     => self::IS_PRESELECTED,
                    'xsi:type' => 'boolean',
                    'value'    => $preselected
                ]
            ]
        );

        return $this;
    }

    /**
     * Build field action config instance
     *
     * @return ActionInterface|null
     */
    public function build(): ?ActionInterface
    {
        return $this->performBuild() ?? null;
    }

    /**
     * Performs build
     *
     * @return ActionInterface
     */
    private function performBuild(): ActionInterface
    {
        /** @var ConfigClassInterface $class */
        $class = $this->configClassFactory->create(
            [
                'baseType' => FieldModifierInterface::class,
                'name' => EavOptionValue2OptionLabel::class,
                'arguments' => array_merge($this->isMultiselect, $this->eavEntityTypeCode, $this->isPreselected)
            ]
        );

        /** @var ActionInterface $action */
        $action = $this->actionFactory->create();
        $action->setConfigClass($class);

        return $action;
    }
}
