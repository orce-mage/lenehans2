<?php

namespace Amasty\Faq\Model\ResourceModel\Category;

use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Setup\Operation\CreateCategoryTable;

class InsertDummyCategory extends \Amasty\Faq\Model\ResourceModel\AbstractDummy
{
    public function _construct()
    {
        $this->_init(CreateCategoryTable::TABLE_NAME, CategoryInterface::CATEGORY_ID);
    }
}
