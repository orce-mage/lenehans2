<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\CodePool\DataProvider;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CollectionFactory;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create()->addGiftCodeCountColumns();
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case 'qty':
                $filter->setField(
                    new \Zend_Db_Expr('COUNT(code.' . CodeInterface::CODE . ')')
                );
                $this->applyHavingFilter($filter);
                break;
            case 'qty_unused':
                $filter->setField(
                    new \Zend_Db_Expr(
                        'SUM(IF(code.' . CodeInterface::STATUS . ' = '
                        . Status::AVAILABLE . ',1,0))'
                    )
                );
                $this->applyHavingFilter($filter);
                break;
            default:
                parent::addFilter($filter);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    private function applyHavingFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getConditionType()) {
            case "gteq":
                $this->collection->getSelect()->having($filter->getField() . ' >= ?', $filter->getValue());
                break;
            case "lteq":
                $this->collection->getSelect()->having($filter->getField() . ' <= ?', $filter->getValue());
                break;
        }
    }
}
