<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mage24Fix
 */

declare(strict_types=1);

namespace Amasty\Mage24Fix\Plugin\Catalog\ViewModel\Product;

use Magento\Catalog\ViewModel\Product\Breadcrumbs as MagentoBreadcrumbs;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BreadcrumbsPlugin
{
    const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';
    const XML_PATH_PRODUCT_USE_CATEGORIES = 'catalog/seo/product_use_categories';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MagentoBreadcrumbs $subject
     * @param callable $proceed
     * @return string|null
     */
    public function aroundGetCategoryUrlSuffix(MagentoBreadcrumbs $subject, callable $proceed): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param MagentoBreadcrumbs $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsCategoryUsedInProductUrl(MagentoBreadcrumbs $subject, callable $proceed): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_USE_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
