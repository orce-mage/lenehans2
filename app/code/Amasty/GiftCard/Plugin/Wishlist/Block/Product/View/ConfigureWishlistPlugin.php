<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Plugin\Wishlist\Block\Product\View;

use Magento\Framework\View\Element\Template;

/**
 * Plugin to allow add amgiftcard with price to wishlist
 */
class ConfigureWishlistPlugin
{
    /**
     * @param Template $subject
     * @param $result
     *
     * @return array
     */
    public function afterGetWishlistOptions(Template $subject, array $result): array
    {
        return array_merge($result, ['amgiftcardInfo' => '[id^=am_giftcard]']);
    }
}
