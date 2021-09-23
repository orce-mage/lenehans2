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



namespace Mirasvit\CacheWarmer\Plugin\Cli;

use Mirasvit\CacheWarmer\Api\Service\CliStoreCurrencyServiceInterface;

class StoreCurrencyStorePlugin
{
    /**
     * @var CliStoreCurrencyServiceInterface
     */
    private $cliStoreCurrencyService;

    /**
     * StoreCurrencyStorePlugin constructor.
     * @param CliStoreCurrencyServiceInterface $cliStoreCurrencyService
     */
    public function __construct(
        CliStoreCurrencyServiceInterface $cliStoreCurrencyService
    ) {
        $this->cliStoreCurrencyService = $cliStoreCurrencyService;
    }

    /**
     * @param \Magento\Store\Model\Store $subject
     * @param string                     $code
     * @return string
     */
    public function afterGetCurrentCurrencyCode($subject, $code)
    {
        if ($storeCurrencyCode = $this->cliStoreCurrencyService->getStoreCurrencyCodeFromUserAgent()) {
            return $storeCurrencyCode;
        }

        return $code;
    }
}
