<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source\Type\Xml;

use Magento\Framework\DataObject;

class Config extends DataObject implements ConfigInterface
{
    const ITEM_PATH = 'item_path';

    public function getItemPath()
    {
        return $this->getData(self::ITEM_PATH);
    }

    public function setItemPath($itemPath)
    {
        $this->setData(self::ITEM_PATH, $itemPath);
    }
}
