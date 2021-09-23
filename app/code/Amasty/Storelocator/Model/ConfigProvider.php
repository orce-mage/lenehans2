<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Scope config Provider model
 */
class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    const PATH_PREFIX = 'amlocator';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_NEW_PAGE = 'general/new_page';
    const REVIEWS_ENABLED = 'general/location_reviews';
    const XPATH_API_KEY = 'general/api';
    const XPATH_LINK_TEXT = 'general/linktext';
    const XPATH_ENABLE_PAGES = 'general/enable_pages';
    const XPATH_LABEL = 'general/label';
    const XPATH_ADD_LINK = 'general/add_to_toolbar_menu';

    const XPATH_CLUSTERING = 'geoip/clustering';
    const XPATH_SUGGESTION_CLICK_SEARCH = 'geoip/suggestion_click_search';
    const XPATH_ZOOM = 'geoip/zoom';
    const XPATH_USE_BROWSER = 'geoip/usebrowserip';
    const XPATH_USE_GEOIP = 'geoip/use';
    const XPATH_AUTOMATIC_LOCATE = 'geoip/automatic_locate';

    const META_TITLE = 'locator/main_settings/meta_title';
    const META_DESCRIPTION = 'locator/main_settings/meta_description';
    const XPATH_PAGINATION_LIMIT = 'locator/main_settings/pagination_limit';
    const XPATH_ALLOWED_COUNTRIES = 'locator/main_settings/allowed_countries';
    const XPATH_URL = 'locator/main_settings/url';
    const XPATH_DESCRIPTION_LIMIT = 'locator/main_settings/description_limit';

    const XPATH_CLOSED_TEXT = 'locator/store_list_settings/close_text';
    const XPATH_CONVERT_TIME = 'locator/store_list_settings/convert_time';
    const XPATH_BREAK_TEXT = 'locator/store_list_settings/break_time_text';
    const XPATH_COUNT_DISTANCE = 'locator/store_list_settings/count_distance';
    const XPATH_COLLAPSE_FILTER = 'locator/store_list_settings/collapse_filter';

    const XPATH_STORE_LIST_TEMPLATE = 'locator/visual_settings/store_list_template';
    const XPATH_TEMPLATE = 'locator/visual_settings/template';
    const XPATH_DISTANCE = 'locator/visual_settings/distance';
    const XPATH_RADIUS = 'locator/visual_settings/radius';
    const XPATH_RADIUS_TYPE = 'locator/visual_settings/radius_type';
    const XPATH_RADIUS_MAX_VALUE = 'locator/visual_settings/radius_max_value';
    const XPATH_RADIUS_MIN_VALUE = 'locator/visual_settings/radius_min_value';

    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaTitle($scopeCode = null)
    {
        return $this->getValue(self::META_TITLE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getMetaDescription($scopeCode = null)
    {
        return $this->getValue(self::META_DESCRIPTION, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isReviewsEnabled($scopeCode = null)
    {
        return (bool)$this->getValue(self::REVIEWS_ENABLED, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getPaginationLimit($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_PAGINATION_LIMIT, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getStoreListTemplate($scopeCode = null)
    {
        return $this->getValue(self::XPATH_STORE_LIST_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getLocatorTemplate($scopeCode = null)
    {
        return $this->getValue(self::XPATH_TEMPLATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getUseBrowser($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_USE_BROWSER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getConvertTime($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_CONVERT_TIME, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getUseGeo($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_USE_GEOIP, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getClustering($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_CLUSTERING, $scopeCode);
    }

    /**
     * @return bool
     */
    public function getSuggestionClickSearch()
    {
        return (bool)$this->getValue(self::XPATH_SUGGESTION_CLICK_SEARCH);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getCountDistance($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_COUNT_DISTANCE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getCollapseFilter($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_COLLAPSE_FILTER, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getAllowedCountries($scopeCode = null)
    {
        return $this->getValue(self::XPATH_ALLOWED_COUNTRIES, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getClosedText($scopeCode = null)
    {
        $closedText = $this->getValue(self::XPATH_CLOSED_TEXT, $scopeCode);

        return $closedText ? $closedText : __('Closed')->getText();
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getBreakText($scopeCode = null)
    {
        $breakText = $this->getValue(self::XPATH_BREAK_TEXT, $scopeCode);

        return $breakText ? $breakText : __('Break Time')->getText();
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getZoom($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_ZOOM, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getAutomaticLocate($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_AUTOMATIC_LOCATE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getApiKey($scopeCode = null)
    {
        return $this->getValue(self::XPATH_API_KEY, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDistanceConfig($scopeCode = null)
    {
        return $this->getValue(self::XPATH_DISTANCE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getRadius($scopeCode = null)
    {
        return $this->getValue(self::XPATH_RADIUS, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getRadiusType($scopeCode = null)
    {
        return $this->getValue(self::XPATH_RADIUS_TYPE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getOpenNewPage($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_NEW_PAGE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getLinkText($scopeCode = null)
    {
        $linkText = $this->getValue(self::XPATH_LINK_TEXT, $scopeCode);

        return $linkText ? $linkText : __('Available In Stores')->getText();
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getUrl($scopeCode = null)
    {
        return $this->getValue(self::XPATH_URL, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getMaxRadiusValue($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_RADIUS_MAX_VALUE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getMinRadiusValue($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_RADIUS_MIN_VALUE, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function getEnablePages($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_ENABLE_PAGES, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return int
     */
    public function getDescriptionLimit($scopeCode = null)
    {
        return (int)$this->getValue(self::XPATH_DESCRIPTION_LIMIT, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getLabel($scopeCode = null)
    {
        $label = $this->getValue(self::XPATH_LABEL, $scopeCode);

        return $label ? $label : __('Store Locator')->getText();
    }

    /**
     * @param null $scopeCode
     *
     * @return bool
     */
    public function isAddLinkToToolbar($scopeCode = null)
    {
        return (bool)$this->getValue(self::XPATH_ADD_LINK, $scopeCode);
    }
}
