<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Icon;

/**
 * @api
 */
interface UploadIconInterface
{
    /**
     * Upload icon file and link it to Stockstatus setting
     *
     * @param int $optionId
     * @param int $storeId
     * @param array $file
     */
    public function execute(int $optionId, int $storeId, array $file): void;
}
