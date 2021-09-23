<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageBig\Shopbybrand\Block\Adminhtml\DataProviders;

/**
 * Provides additional data for image uploader
 */
class ImageUploadConfig
{

    /**
     * Get image resize configuration
     *
     * @return int
     */
    public function getIsResizeEnabled(): int
    {
        return 1;
    }
}
