<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code\ResourceModel;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCard\Model\Code\Code::class,
            \Amasty\GiftCard\Model\Code\ResourceModel\Code::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param int $codePoolId
     *
     * @return int
     */
    public function countOfFreeCodesByCodeSet(int $codePoolId)
    {
        $this->addFieldToFilter(CodeInterface::STATUS, Status::AVAILABLE)
            ->addFieldToFilter(CodeInterface::CODE_POOL_ID, $codePoolId);

        return $this->count();
    }
}
