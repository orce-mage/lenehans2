<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Relation;

interface RelationValidationInterface
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
}
