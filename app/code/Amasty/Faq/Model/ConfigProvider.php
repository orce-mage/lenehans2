<?php

namespace Amasty\Faq\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

/**
 * Scope config Provider model
 */
class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'amastyfaq/';
    const PATH_PREFIX = 'amastyfaq/';

    const ENABLED = 'general/enabled';
    const URL_KEY_PATH = 'general/url_key';
    const ADD_TO_TOOLBAR = 'general/add_to_toolbar_menu';
    const ADD_TO_FOOTER = 'general/add_to_footer_menu';
    const LIMIT_SHORT_ANSWER = 'faq_page/limit_short_answer';
    const USER_NOTIFY = 'user_email/user_notify';
    const ALLOW_UNREGISTERED_CUSTOMER_ASK  = 'general/unregistered_customers_questions';
    const USER_NOTIFY_SENDER = 'user_email/sender';
    const USER_NOTIFY_EMAIL_TEMPLATE = 'user_email/template';
    const ADMIN_NOTIFY = 'admin_email/enable_notify';
    const ADMIN_NOTIFY_EMAIL = 'admin_email/send_to';
    const ADMIN_NOTIFY_EMAIL_TEMPLATE = 'admin_email/template';
    const CATEGORIES_SORT = 'faq_page/category_sort';
    const QUESTIONS_SORT = 'faq_page/question_sort';
    const IS_SHOW_SEARCH = 'faq_page/show_search';
    const SHOW_TAB_ON_PRODUCT_PAGE = 'product_page/show_tab';
    const SHOW_ASK_QUESTION_FORM_ON_PRODUCT_PAGE = 'product_page/show_link';
    const SHOW_ASK_QUESTION_FORM_ON_ANSWER_PAGE = 'faq_page/show_ask';
    const SHOW_BREADCRUMBS = 'faq_page/show_breadcrumbs';
    const LABEL = 'general/label';
    const LABEL_NO_RESULT = 'faq_page/no_result';
    const ADD_TO_MAIN_MENU = 'general/add_to_category_menu';
    const IS_RATING_ENABLED = 'rating/enabled';
    const RATING_TEMPLATE = 'rating/type';
    const IS_SITEMAP_ENABLED = 'seo/xml/sitemap';
    const IS_HREFLANG_ENABLED = 'seo/xml/hreflang';
    const HREFLANG_LANGUAGE = 'seo/xml/language';
    const HREFLANG_COUNTRY = 'seo/xml/country';
    const CHANGE_FREQUENCY = 'seo/xml/changefreq';
    const SITEMAP_PRIORITY = 'seo/xml/sitemap_priority';
    const RATING_HIDE_EMPTY_TOTAL = 'rating/avg_hide_empty_total';
    const RATING_ALLOW_GUESTS = 'rating/allow_guests';
    const ADD_URL_SUFFIX = 'seo/add_url_suffix';
    const REMOVE_TRAILING_SLASH = 'seo/remove_trailing_slash';
    const URL_SUFFIX = 'seo/url_suffix';
    const CANONICAL_URL = 'seo/canonical_url';
    const ADD_STRUCTUREDDATA = 'seo/add_structureddata';
    const ADD_RICHDATA_BREADCRUMBS = 'seo/add_richdata_breadcrumbs';
    const ADD_RICHDATA_ORGANIZATION = 'seo/add_richdata_organization';
    const RICHDATA_ORGANIZATION_WEBSITE_URL = 'seo/organization_website_url';
    const RICHDATA_ORGANIZATION_LOGO_URL = 'seo/organization_logo_url';
    const RICHDATA_ORGANIZATION_NAME = 'seo/organization_name';
    const ADD_RICHDATA_CONTACT = 'seo/add_richdata_contact';
    const RICHDATA_ORGANIZATION_CONTACT_TYPE = 'seo/organization_contact_type';
    const RICHDATA_ORGANIZATION_TELEPHONE = 'seo/organization_telephone';
    const CATEGORY_SEARCH = 'faq_page/category_in_search';
    const LIMIT_CATEGORY_SEARCH = 'faq_page/limit_category_search';
    const SEARCH_PAGE_SIZE = 'faq_page/limit_question_search';
    const CATEGORY_PAGE_SIZE = 'faq_page/limit_question_category';
    const PRODUCT_PAGE_SIZE = 'product_page/limit_question_product';
    const SOCIAL_ENABLED = 'social/enabled';
    const SOCIAL_ACTIVE_BUTTONS = 'social/buttons';
    const PAGE_LAYOUT = 'faq_home_page/layout';
    const FAQ_PAGE_SHORT_ANSWER_BEHAVIOR = 'faq_page/short_answer_behavior';
    const PRODUCT_PAGE_SHORT_ANSWER_BEHAVIOR = 'product_page/short_answer_behavior';
    const FAQ_CMS_HOME_PAGE = 'faq_home_page/cmspages_faq_home_page';
    const USE_FAQ_CMS_HOME_PAGE = 'faq_home_page/use_faq_home_page';
    const GDPR_ENABLED = 'gdpr/enabled';
    const GDPR_TEXT = 'gdpr/text';
    const TAG_MENU_LIMIT = 'faq_page/tag_menu_limit';
    const TAB_NAME = 'product_page/tab_name';
    const TAB_POSITION = 'product_page/tab_position';

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAllowUnregisteredCustomersAsk($storeId = null)
    {
        return (bool)$this->getValue(self::ALLOW_UNREGISTERED_CUSTOMER_ASK, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getUrlKey($storeId = null)
    {
        return $this->getValue(self::URL_KEY_PATH, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getLimitShortAnswer($storeId = null)
    {
        return (int)$this->getValue(self::LIMIT_SHORT_ANSWER, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNotifyUser($storeId = null)
    {
        return (bool)$this->getValue(self::USER_NOTIFY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getNotifySender($storeId = null)
    {
        return $this->getValue(self::USER_NOTIFY_SENDER, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNotifyAdmin($storeId = null)
    {
        return (bool)$this->getValue(self::ADMIN_NOTIFY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function notifyAdminEmail($storeId = null)
    {
        return $this->getValue(self::ADMIN_NOTIFY_EMAIL, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getCategoriesSort($storeId = null)
    {
        return $this->getValue(self::CATEGORIES_SORT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getQuestionsSort($storeId = null)
    {
        return $this->getValue(self::QUESTIONS_SORT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowSearchBox($storeId = null)
    {
        return (bool)$this->getValue(self::IS_SHOW_SEARCH, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowAskQuestionOnAnswerPage($storeId = null)
    {
        return (bool)$this->getValue(self::SHOW_ASK_QUESTION_FORM_ON_ANSWER_PAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowAskQuestionOnProductPage($storeId = null)
    {
        return (bool)$this->getValue(self::SHOW_ASK_QUESTION_FORM_ON_PRODUCT_PAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowBreadcrumbs($storeId = null)
    {
        return (bool)$this->getValue(self::SHOW_BREADCRUMBS);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getLabel($storeId = null)
    {
        return $this->getValue(self::LABEL, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getNoItemsLabel($storeId = null)
    {
        return $this->getValue(self::LABEL_NO_RESULT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddToFooter($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_TO_FOOTER, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddToToolbar($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_TO_TOOLBAR, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddToMainMenu($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_TO_MAIN_MENU, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isRatingEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::IS_RATING_ENABLED, $storeId);
    }

    public function getVotingBehavior($storeId = null): string
    {
        switch ($this->getValue(self::RATING_TEMPLATE, $storeId)) {
            case \Amasty\Faq\Model\OptionSource\Question\RatingType::VOTING:
                $behavior = 'voting';
                break;
            case \Amasty\Faq\Model\OptionSource\Question\RatingType::AVERAGE:
                $behavior = 'average';
                break;
            case \Amasty\Faq\Model\OptionSource\Question\RatingType::YESNO:
            default:
                $behavior = 'yesno';
                break;
        }

        return $behavior;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRatingTemplateName($storeId = null)
    {
        return 'Amasty_Faq/rating/' . $this->getVotingBehavior($storeId);
    }

    public function isHideZeroRatingTotal($storeId = null): bool
    {
        return  (bool)$this->getValue(self::RATING_HIDE_EMPTY_TOTAL, $storeId);
    }

    public function isGuestRatingAllowed($storeId = null): bool
    {
        return  (bool)$this->getValue(self::RATING_ALLOW_GUESTS, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isSiteMapEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::IS_SITEMAP_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isHreflangEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::IS_HREFLANG_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getHreflangLanguage($storeId = null)
    {
        return $this->getValue(self::HREFLANG_LANGUAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getHreflangCountry($storeId = null)
    {
        return $this->getValue(self::HREFLANG_COUNTRY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getFrequency($storeId = null)
    {
        return $this->getValue(self::CHANGE_FREQUENCY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSitemapPriority($storeId = null)
    {
        return $this->getValue(self::SITEMAP_PRIORITY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddUrlSuffix($storeId = null)
    {
        return $this->isSetFlag(self::ADD_URL_SUFFIX, $storeId);
    }

    /**
     * @return bool
     */
    public function isRemoveTrailingSlash()
    {
        return $this->isSetFlag(self::REMOVE_TRAILING_SLASH);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->getValue(self::URL_SUFFIX, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isCanonicalUrlEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::CANONICAL_URL, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isSocialButtonsEnabled($storeId = null)
    {
        return $this->isSetFlag(self::SOCIAL_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getSocialActiveButtons($storeId = null)
    {
        return explode(',', $this->getValue(self::SOCIAL_ACTIVE_BUTTONS, $storeId));
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddStructuredData($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_STRUCTUREDDATA, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddRichDataBreadcrumbs($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_RICHDATA_BREADCRUMBS, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddRichDataOrganization($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_RICHDATA_ORGANIZATION, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRichDataWebsiteUrl($storeId = null)
    {
        return $this->getValue(self::RICHDATA_ORGANIZATION_WEBSITE_URL, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRichDataLogoUrl($storeId = null)
    {
        return $this->getValue(self::RICHDATA_ORGANIZATION_LOGO_URL, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRichDataOrganizationName($storeId = null)
    {
        return $this->getValue(self::RICHDATA_ORGANIZATION_NAME, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAddRichDataContact($storeId = null)
    {
        return (bool)$this->getValue(self::ADD_RICHDATA_CONTACT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRichDataContactType($storeId = null)
    {
        return $this->getValue(self::RICHDATA_ORGANIZATION_CONTACT_TYPE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getRichDataTelephone($storeId = null)
    {
        return $this->getValue(self::RICHDATA_ORGANIZATION_TELEPHONE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getProductPageSize($storeId = null)
    {
        return (int)$this->getValue(self::PRODUCT_PAGE_SIZE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getCategoryPageSize($storeId = null)
    {
        return (int)$this->getValue(self::CATEGORY_PAGE_SIZE, $storeId);
    }

    /**
     * @return bool
     */
    public function isShowCategoryInSearch()
    {
        return (bool)$this->getValue(self::CATEGORY_SEARCH);
    }

    /**
     * @return int
     */
    public function getLimitCategoryInSearch()
    {
        return (int)$this->getValue(self::LIMIT_CATEGORY_SEARCH);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getSearchPageSize($storeId = null)
    {
        return (int)$this->getValue(self::SEARCH_PAGE_SIZE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getFaqPageShortAnswerBehavior($storeId = null)
    {
        return (int)$this->getValue(self::FAQ_PAGE_SHORT_ANSWER_BEHAVIOR, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getProductPageShortAnswerBehavior($storeId = null)
    {
        return (int)$this->getValue(self::PRODUCT_PAGE_SHORT_ANSWER_BEHAVIOR, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getPageLayout($storeId = null)
    {
        return $this->getValue(self::PAGE_LAYOUT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isUseFaqCmsHomePage($storeId = null)
    {
        return (bool)$this->getValue(self::USE_FAQ_CMS_HOME_PAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getFaqCmsHomePage($storeId = null)
    {
        return $this->getValue(self::FAQ_CMS_HOME_PAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isGDPREnabled($storeId = null)
    {
        return (bool)$this->getValue(self::GDPR_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getGDPRText($storeId = null)
    {
        return $this->getValue(self::GDPR_TEXT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getTagMenuLimit($storeId = null)
    {
        return (int)$this->getValue(self::TAG_MENU_LIMIT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTabName($storeId = null)
    {
        return $this->getValue(self::TAB_NAME, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getTabPosition($storeId = null)
    {
        return (int)$this->getValue(self::TAB_POSITION, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowTab($storeId = null)
    {
        return (bool)$this->getValue(self::SHOW_TAB_ON_PRODUCT_PAGE, $storeId);
    }
}
