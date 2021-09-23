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



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\App\Helper\Context as ContextHelper;
use Mirasvit\CacheWarmer\Api\Data\UserAgentInterface;
use Mirasvit\CacheWarmer\Api\Service\CliStoreCurrencyServiceInterface;

class CliStoreCurrencyService implements CliStoreCurrencyServiceInterface
{
    /**
     * @var ContextHelper
     */
    private $contextHelper;

    /**
     * CliStoreCurrencyService constructor.
     * @param ContextHelper $contextHelper
     */
    public function __construct(
        ContextHelper $contextHelper
    ) {
        $this->contextHelper = $contextHelper;
    }

    /**
     * @return string
     */
    public function getStoreCurrencyCodeFromUserAgent()
    {
        $storeCurrency = '';
        $userAgent     = $this->contextHelper->getHttpHeader()->getHttpUserAgent();
        if (strpos($userAgent, UserAgentInterface::STORE_CURRENCY_BEGIN_TAG) !== false
            && strpos($userAgent, UserAgentInterface::STORE_CURRENCY_END_TAG) !== false) {
            $pattern = '/' . UserAgentInterface::STORE_CURRENCY_BEGIN_TAG
                . '(.*?)' . UserAgentInterface::STORE_CURRENCY_END_TAG . '/ims';
            preg_match($pattern, $userAgent, $storeCurrencyArray);
            if (isset($storeCurrencyArray[1])) {
                $storeCurrency = $storeCurrencyArray[1];
            }
        }

        return $storeCurrency;
    }
}
