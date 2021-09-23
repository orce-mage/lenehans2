<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration\TestModule\Model\ResourceModel;

use Amasty\ExportCore\Test\Integration\TestModule\Model\TestEntity1 as TestEntity1Model;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TestEntity1 extends AbstractDb
{
    const TABLE_NAME = 'amasty_export_test_entity1';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, TestEntity1Model::ID);
    }
}
