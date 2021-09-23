<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration\TestModule\Model\ResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\ExportCore\Test\Integration\TestModule\Model\TestEntity1::class,
            TestEntity1::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
