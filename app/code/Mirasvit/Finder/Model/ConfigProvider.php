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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider
{
    const REQUEST_VAR     = 'finder';
    const FORMAT_MINUS    = 1;
    const FORMAT_SLASH    = 2;
    const DELIMITER_MINUS = '-';
    const DELIMITER_SLASH = '/';

    private $scopeConfig;

    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
    }

    public function getBaseRoute(): string
    {
        return 'mst_finder';
    }

    public function getResultRoute(): string
    {
        return $this->getBaseRoute() . '/finder/index';
    }

    public function isFriendlyUrl(): bool
    {
        return (bool)$this->scopeConfig->getValue('mst_finder/url/is_friendly_url', ScopeInterface::SCOPE_STORE);
    }

    public function getUrlFormat(): string
    {
        return (string)$this->scopeConfig->getValue('mst_finder/url/url_format', ScopeInterface::SCOPE_STORE);
    }

    public function getFilterDelimiter(): string
    {
        if ($this->getUrlFormat() == self::FORMAT_SLASH) {
            return self::DELIMITER_SLASH;
        }
        if ($this->getUrlFormat() == self::FORMAT_MINUS) {
            return self::DELIMITER_MINUS;
        }

        return self::DELIMITER_SLASH;
    }

    public function getCategorySuffix(): string
    {
        return (string)$this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
