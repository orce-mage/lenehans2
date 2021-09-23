<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\Rule as RuleModel;
use Amasty\Stockstatus\Model\ResourceModel\Rule as RuleResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = RuleInterface::ID;

    public function _construct()
    {
        $this->_init(RuleModel::class, RuleResource::class);
    }
}
