<?php

namespace Amasty\Faq\Api\Data;

interface CategoryInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const CATEGORY_ID = 'category_id';
    const TITLE = 'title';
    const PAGE_TITLE = 'page_title';
    const POSITION = 'position';
    const URL_KEY = 'url_key';
    const STATUS = 'status';
    const META_TITLE = 'meta_title';
    const META_DESCRIPTION = 'meta_description';
    const VISIT_COUNT = 'visit_count';
    const EXCLUDE_SITEMAP = 'exclude_sitemap';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CANONICAL_URL = 'canonical_url';
    const NOINDEX = 'noindex';
    const NOFOLLOW = 'nofollow';
    const DESCRIPTION = 'description';
    const ICON = 'icon';
    const STORES = 'store_ids';
    const QUESTIONS = 'questions';
    const CUSTOMER_GROUPS = 'customer_groups';

    /**#@-*/

    /**
     * @return int
     */
    public function getCategoryId();

    /**
     * @param int $categoryId
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setCategoryId($categoryId);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getPageTitle();

    /**
     * @param string $title
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setPageTitle($title);

    /**
     * @return string
     */
    public function getRelativeUrl();

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * @param int|null $position
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setPosition($position);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param string $urlKey
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * @param string|null $metaTitle
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $metaDescription
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return int
     */
    public function getVisitCount();

    /**
     * @param int $count
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setVisitCount($count);

    /**
     * @return bool
     */
    public function getExcludeSitemap();

    /**
     * @param bool $exclude
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setExcludeSitemap($exclude);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param $canonicalUrl
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setCanonicalUrl($canonicalUrl);

    /**
     * @return string
     */
    public function getCanonicalUrl();

    /**
     * @return bool
     */
    public function isNoindex();

    /**
     * @return bool
     */
    public function isNofollow();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param string $icon
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setIcon($icon);

    /**
     * @return string
     */
    public function getStores();

    /**
     * @param string $stores
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setStores($stores);

    /**
     * @return string
     */
    public function getQuestions();

    /**
     * @param string $questions
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function setQuestions($questions);

    /**
     * @return string
     */
    public function getCustomerGroups();

    /**
     * @param string $customerGroups
     *
     * @return CategoryInterface
     */
    public function setCustomerGroups(string $customerGroups);
}
