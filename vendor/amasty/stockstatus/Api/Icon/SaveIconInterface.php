<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

use Amasty\Stockstatus\Api\Data\IconInterface;
use Amasty\Stockstatus\Model\Icon;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface SaveIconInterface
{
    /**
     * Save Stockstatus settings icon
     *
     * @param IconInterface|Icon $icon
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(IconInterface $icon): void;
}
