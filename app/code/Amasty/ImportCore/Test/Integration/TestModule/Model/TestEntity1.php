<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\TestModule\Model;

use Magento\Framework\Model\AbstractModel;

class TestEntity1 extends AbstractModel
{
    const ID = 'id';
    const FIELD_1 = 'field_1';
    const FIELD_2 = 'field_2';
    const FIELD_3 = 'field_3';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\TestEntity1::class);
        $this->setIdFieldName(self::ID);
    }
}
