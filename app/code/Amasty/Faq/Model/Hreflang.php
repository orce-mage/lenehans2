<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Model\Config\Hreflang\Country;
use Amasty\Faq\Model\Config\Hreflang\Language;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Hreflang
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Faq\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var Config
     */
    private $pageConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ConfigProvider $configProvider,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Config $pageConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configProvider = $configProvider;
        $this->pageConfig = $pageConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $url
     * @param array $stores
     *
     * @return void
     */
    public function addHreflang($url, $stores)
    {
        foreach ($this->storeManager->getStores() as $store) {
            $storeId = $store->getId();
            if ($this->configProvider->isHreflangEnabled($storeId)
                && (in_array($storeId, $stores) || empty($stores))
            ) {
                $hreflang = $this->getHreflangString($storeId);
                $this->pageConfig->addRemotePageAsset(
                    $store->getBaseUrl() . $url,
                    'hreflang',
                    ['attributes' => ['hreflang' => $hreflang, 'rel' => 'alternate']]
                );
                if ($storeId == $this->storeManager->getDefaultStoreView()->getId()) {
                    // add x-default hreflang
                    $this->pageConfig->addRemotePageAsset(
                        $store->getBaseUrl() . $url,
                        'hreflang',
                        ['attributes' => ['hreflang' => Language::CODE_XDEFAULT, 'rel' => 'alternate']]
                    );
                }
            }
        }
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getHreflangString($storeId)
    {
        $hreflangString = $this->getLanguageByStore($storeId);
        if ($this->getCountryCodeByStore($storeId) !== Country::DONT_ADD) {
            $hreflangString .= '-' . $this->getCountryCodeByStore($storeId);
        }

        return $hreflangString;
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getLanguageByStore($storeId)
    {
        $language = $this->configProvider->getHreflangLanguage($storeId);
        if ($language == Language::CURRENT_STORE) {
            $currentLocale = $this->scopeConfig->getValue(
                DirectoryHelper::XML_PATH_DEFAULT_LOCALE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $currentLocalArray = explode('_', $currentLocale);
            $language = array_shift($currentLocalArray);
        }

        return $language;
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getCountryCodeByStore($storeId)
    {
        $countryCode = $this->configProvider->getHreflangCountry($storeId);
        if ($countryCode == Country::CURRENT_STORE) {
            $countryCode = $this->scopeConfig->getValue(
                DirectoryHelper::XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $countryCode;
    }
}
