<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\DataProvider;

use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\OrderHistoryCollectionGenerator;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class OrderHistoryListing extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        OrderHistoryCollectionGenerator $historyCollectionGenerator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $historyCollectionGenerator->getOrderCollectionByAccountId(
            (int)$request->getParam('account_id')
        );
    }
}
