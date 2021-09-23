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



namespace Mirasvit\CacheWarmer\Api\Data;

interface UserAgentInterface
{
    const STORE_CURRENCY_BEGIN_TAG = 'm__warmer_store_currency_begin';
    const STORE_CURRENCY_END_TAG   = 'm__warmer_store_currency_end';

    const MOBILE_USER_AGENT
        = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) '
        . 'AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1 (m__warmer)';

    const DESKTOP_USER_AGENT
        = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 (m__warmer)';

    const SITEMAP_USER_AGENT
        = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 (m__warmer_sitemap)';

    const FILE_USER_AGENT
        = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36 (m__warmer_file)';
}
