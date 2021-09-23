<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Plugin\HolePunch;

use Mirasvit\CacheWarmer\Service\Config\HolePunchConfig;

class ProductImagePlugin
{
    /**
     *  Need for cms blocks excluding
     * @param \Magento\Catalog\Model\Product\Image $subject
     * @param null|string                         $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetDestinationSubdir($subject, $result)
    {
        /** mp comment start */
        if (!$result
            && ((isset($GLOBALS[HolePunchConfig::CMS_BLOCK_EXCLUDE])
                    && $GLOBALS[HolePunchConfig::CMS_BLOCK_EXCLUDE])
                || (isset($GLOBALS[HolePunchConfig::WIDGET_BLOCK_EXCLUDE])
                    && $GLOBALS[HolePunchConfig::WIDGET_BLOCK_EXCLUDE]))) {
            return 'small_image';
        }
        /** mp comment end */
        return $result;
    }
}
