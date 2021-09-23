<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Backend\StockstatusSettings\Form\Image\Uploader\Validator;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension as MagentoNotProtectedExtension;

class NotProtectedExtension extends MagentoNotProtectedExtension
{
    /**
     * Allow svg file extension
     *
     * @param null|int $store
     * @return string|string[]
     */
    public function getProtectedFileExtensions($store = null)
    {
        $result = parent::getProtectedFileExtensions($store);
        unset($result['svg']);

        return $result;
    }
}
