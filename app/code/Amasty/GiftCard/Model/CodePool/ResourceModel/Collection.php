<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\ResourceModel;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCard\Model\CodePool\CodePool::class,
            \Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Format columns for display in listing
     *
     * @return Collection
     */
    public function addGiftCodeCountColumns(): Collection
    {
        $codeTable = $this->getTable(\Amasty\GiftCard\Model\Code\ResourceModel\Code::TABLE_NAME);
        $this->getSelect()->joinLeft(
            ['code' => $codeTable],
            'main_table.' . CodePoolInterface::CODE_POOL_ID . ' = code.' . CodeInterface::CODE_POOL_ID,
            [
                'qty' => new \Zend_Db_Expr('COUNT(code.' . CodeInterface::CODE . ')'),
                'qty_unused' => new \Zend_Db_Expr(
                    'SUM(IF(code.' . CodeInterface::STATUS . ' = '
                    . Status::AVAILABLE . ',1,0))'
                )
            ]
        )->group('main_table.' . CodePoolInterface::CODE_POOL_ID);

        return $this;
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray(CodePoolInterface::CODE_POOL_ID, CodePoolInterface::TITLE);
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->getSelect()->getPart(Select::HAVING)) {
            $countSelect->reset();
            $group = $this->getSelect()->getPart(Select::GROUP);
            $countSelect->from(
                ['main_table' => $this->getSelect()],
                [new \Zend_Db_Expr("COUNT(DISTINCT " . implode(", ", $group) . ")")]
            );
        }

        return $countSelect;
    }
}
