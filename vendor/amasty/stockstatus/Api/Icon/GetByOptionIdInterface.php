<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface GetByOptionIdInterface
{
    /**
     * Load icon from Stockstatus settings
     *
     * @param int $optionId
     * @param int $storeId
     * @return IconInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $optionId, int $storeId): IconInterface;
}
