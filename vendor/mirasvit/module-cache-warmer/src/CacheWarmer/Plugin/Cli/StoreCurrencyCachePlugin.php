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

use Magento\Framework\App\Http\Context;
use Mirasvit\CacheWarmer\Api\Service\CliStoreCurrencyServiceInterface;

class StoreCurrencyCachePlugin
{
    /**
     * @var CliStoreCurrencyServiceInterface
     */
    private $cliStoreCurrencyService;

    /**
     * StoreCurrencyCachePlugin constructor.
     * @param CliStoreCurrencyServiceInterface $cliStoreCurrencyService
     */
    public function __construct(
        CliStoreCurrencyServiceInterface $cliStoreCurrencyService
    ) {
        $this->cliStoreCurrencyService = $cliStoreCurrencyService;
    }

    /**
     * @param \Magento\Framework\App\Http\Context $subject
     * @param array                               $data
     * @return array
     */
    public function afterGetData($subject, $data)
    {
        if ($storeCurrencyCode = $this->cliStoreCurrencyService->getStoreCurrencyCodeFromUserAgent()) {
            $data[Context::CONTEXT_CURRENCY] = $storeCurrencyCode;
        }

        return $data;
    }
}
