<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\FileDestination;

use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\App\RequestInterface;

interface FileDestinationMetaInterface
{
    public function getMeta(): array;

    public function prepareConfig(
        ProfileConfigInterface $profileConfig,
        RequestInterface $request
    ): FileDestinationMetaInterface;
}
