<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;

/**
 * @api
 */
interface GetNewInterface
{
    /**
     * Get new empty Stockstatus icon model
     *
     * @return IconInterface
     */
    public function execute(): IconInterface;
}
