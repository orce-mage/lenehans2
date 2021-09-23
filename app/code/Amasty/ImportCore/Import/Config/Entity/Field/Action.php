<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Entity\Field;

use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface;
use Magento\Framework\DataObject;

class Action extends DataObject implements ActionInterface
{
    const ACTION_CLASS = 'action_class';
    const ACTION_GROUP = 'action_group';

    /**
     * @inheritDoc
     */
    public function getConfigClass()
    {
        return $this->getData(self::ACTION_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setConfigClass($configClass)
    {
        return $this->setData(self::ACTION_CLASS, $configClass);
    }

    /**
     * @inheritDoc
     */
    public function getGroup()
    {
        return $this->getData(self::ACTION_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function setGroup($group)
    {
        return $this->setData(self::ACTION_GROUP, $group);
    }
}
