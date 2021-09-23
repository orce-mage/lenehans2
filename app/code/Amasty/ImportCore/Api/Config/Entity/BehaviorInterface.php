<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Entity;

interface BehaviorInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     *
     * @return void
     */
    public function setCode($code);

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
     * @return array
     */
    public function getExecuteOnCodes();

    /**
     * @param array $behaviorCodes
     * @return void
     */
    public function setExecuteOnCodes(array $behaviorCodes);

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
}
