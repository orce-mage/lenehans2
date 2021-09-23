<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Relation;

use Amasty\ImportCore\Api\Config\Relation\RelationActionInterface;
use Magento\Framework\DataObject;

class Action extends DataObject implements RelationActionInterface
{
    const ACTION_CLASS = 'action_class';

    public function getConfigClass()
    {
        return $this->getData(self::ACTION_CLASS);
    }

    public function setConfigClass($configClass)
    {
        $this->setData(self::ACTION_CLASS, $configClass);
    }
}
