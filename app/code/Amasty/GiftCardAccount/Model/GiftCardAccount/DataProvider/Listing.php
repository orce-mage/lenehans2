<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\DataProvider;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $accountCollectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $accountCollectionFactory->create()
            ->addCodeTable()->addOrderTable();
    }

    public function addOrder($field, $direction)
    {
        if ($field === 'sender_email') {
            $field = 'customer_order.customer_email';
        }

        parent::addOrder($field, $direction);
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        switch ($filter->getField()) {
            case GiftCardAccountInterface::STATUS:
                $filter->setField('main_table.' . GiftCardAccountInterface::STATUS);
                break;
            case 'order_number':
                $filter->setField('order.increment_id');
                break;
            case 'sender_email':
                $filter->setField('customer_order.customer_email');
                break;
            default:
                parent::addFilter($filter);
        }
        parent::addFilter($filter);
    }
}
