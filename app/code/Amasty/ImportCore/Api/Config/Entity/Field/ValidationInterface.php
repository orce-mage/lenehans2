<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Entity\Field;

interface ValidationInterface
{
    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface
     */
    public function getConfigClass();

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $configClass
     *
     * @return void
     */
    public function setConfigClass($configClass);

    /**
     * @return string
     */
    public function getError();

    /**
     * @param string $error
     *
     * @return void
     */
    public function setError($error);

    /**
     * @return string[]
     */
    public function getExcludeBehaviors();

    /**
     * @param string[] $behaviorCodes
     *
     * @return void
     */
    public function setExcludeBehaviors($behaviorCodes);

    /**
     * @return string[]
     */
    public function getIncludeBehaviors();

    /**
     * @param string[] $behaviorCodes
     *
     * @return void
     */
    public function setIncludeBehaviors($behaviorCodes);

    /**
     * @return bool
     */
    public function getIsApplyToRootEntityOnly();

    /**
     * @param bool $applyToRootEntityOnly
     * @return void
     */
    public function setIsApplyToRootEntityOnly($applyToRootEntityOnly);
}
