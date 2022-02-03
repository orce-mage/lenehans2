<?php

namespace Amasty\Faq\Model\Import\Category;

use Amasty\Base\Model\Import\Mapping\Mapping as MappingBase;
use Amasty\Faq\Api\ImportExport\CategoryInterface;

class Mapping extends MappingBase implements \Amasty\Base\Model\Import\Mapping\MappingInterface
{
    /**
     * @var array
     */
    protected $mappings = [
        CategoryInterface::CATEGORY_ID,
        CategoryInterface::TITLE,
        CategoryInterface::URL_KEY,
        CategoryInterface::STORE_CODES,
        CategoryInterface::STATUS,
        CategoryInterface::META_TITLE,
        CategoryInterface::META_DESCRIPTION,
        CategoryInterface::POSITION,
        CategoryInterface::QUESTION_IDS
    ];

    /**
     * @var string
     */
    protected $masterAttributeCode = CategoryInterface::TITLE;
}
