<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Config\ConfigClass;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Magento\Framework\DataObject;

class Argument extends DataObject implements ArgumentInterface
{
    const NAME = 'name';
    const VALUE = 'value';
    const TYPE = 'type';
    const ITEMS = 'items';

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
    }

    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    public function setValue($value)
    {
        $this->setData(self::VALUE, $value);
    }

    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    public function setItems($items)
    {
        $this->setData(self::ITEMS, $items);
    }
}
