<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Model\ResourceModel\Range as RangeResource;
use Amasty\Stockstatus\Model\Range as RangeModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = RangeInterface::ID;

    public function _construct()
    {
        $this->_init(RangeModel::class, RangeResource::class);
    }
}
