<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration\TestModule\Model;

use Magento\Framework\Model\AbstractModel;

class TestEntity1 extends AbstractModel
{
    const ID = 'id';
    const TEXT_FIELD = 'text_field';
    const DATE_FIELD = 'date_field';
    const SELECT_FIELD = 'select_field';

    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\TestEntity1::class);
        $this->setIdFieldName(self::ID);
    }
}
