<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Logger\Handler;

use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Class Base
 * @package Wyomind\Framework\Logger\Handler
 */
class Base extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @param DriverInterface $filesystem
     * @param ProductMetadata $productMetaData
     * @param string $filePath
     * @param string $fileName
     * @throws \Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ProductMetadata $productMetaData,
        $filePath = null,
        $fileName = null
    ) {
    
        $explodedVersion = explode("-", $productMetaData->getVersion());
        $magentoVersion = $explodedVersion[0];
        if (version_compare($magentoVersion, "2.2.0", "<")) {
            $filePath = BP . $fileName;
            parent::__construct($filesystem, $filePath);
        } else {
            parent::__construct($filesystem, $filePath, $fileName);
        }
    }
}
