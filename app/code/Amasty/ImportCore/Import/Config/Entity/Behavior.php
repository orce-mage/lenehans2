<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity;

use Amasty\ImportCore\Api\Config\Entity\BehaviorInterface;
use Magento\Framework\DataObject;

class Behavior extends DataObject implements BehaviorInterface
{
    const CODE = 'code';
    const NAME = 'name';
    const EXECUTE_ON_CODES = 'execute_on_codes';
    const CONFIG_CLASS = 'config_class';

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        $this->setData(self::CODE, $code);
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
        $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getExecuteOnCodes()
    {
        return $this->getData(self::EXECUTE_ON_CODES) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setExecuteOnCodes(array $behaviorCodes)
    {
        $this->setData(self::EXECUTE_ON_CODES, $behaviorCodes);
    }

    /**
     * @inheritDoc
     */
    public function getConfigClass()
    {
        return $this->getData(self::CONFIG_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setConfigClass($configClass)
    {
        $this->setData(self::CONFIG_CLASS, $configClass);
    }
}
