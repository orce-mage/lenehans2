<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Mview\View;

use Magento\Framework\Mview\View\Subscription;

class ExistingSubscription extends Subscription
{
    /**
     * @return ExistingSubscription
     */
    public function create()
    {
        if ($this->isSubscriptionTableExist()) {
            parent::create();
        }

        return $this;
    }

    /**
     * @return ExistingSubscription
     */
    public function remove()
    {
        if ($this->isSubscriptionTableExist()) {
            parent::remove();
        }

        return $this;
    }

    public function isSubscriptionTableExist(): bool
    {
        return $this->resource->getConnection()->isTableExists($this->resource->getTableName($this->tableName));
    }
}
