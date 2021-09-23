<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

interface GetIconUrlInterface
{
    /**
     * Get Stockstatus icon url (if exists)
     *
     * @param int $optionId
     * @param int $storeId
     * @return string|null
     */
    public function execute(int $optionId, int $storeId): ?string;
}
