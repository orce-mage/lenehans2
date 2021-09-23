<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\TestModule\Model\ResourceModel;

use Amasty\ImportCore\Test\Integration\TestModule\Model\TestEntity1 as TestEntity1Model;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TestEntity1 extends AbstractDb
{
    const TABLE_NAME = 'amasty_import_test_entity1';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, TestEntity1Model::ID);
    }
}
