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

interface PageInterface
{
    const TABLE_NAME = 'mst_cache_warmer_page';

    const ID            = 'page_id';
    const URI           = 'uri';
    const URI_HASH      = 'uri_hash';
    const CACHE_ID      = 'cache_id';
    const PAGE_TYPE     = 'page_type';
    const PRODUCT_ID    = 'product_id';
    const CATEGORY_ID   = 'category_id';
    const STORE_ID      = 'store_id';
    const VARY_DATA     = 'vary_data';
    const VARY_DATA_HASH     = 'vary_data_hash';
    const USER_AGENT     = 'user_agent';
    const COOKIE     = 'cookie';
    const ATTEMPTS      = 'attempts';
    const POPULARITY    = 'popularity';
    const WARM_RULE_VERSION  = 'warm_rule_version';
    const WARM_RULE_IDS = 'warm_rule_ids';
    const HEADERS       = 'headers';
    const STATUS       = 'status';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';
    const CACHED_AT       = 'cached_at';
    const FLUSHED_AT       = 'flushed_at';


    const STATUS_CACHED       = 'cached';
    const STATUS_PENDING       = 'pending';
    const STATUS_UNCACHEABLE       = 'uncacheable';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getUri();

    /**
     * @param string $value
     * @return $this
     */
    public function setUri($value);

    /**
     * @return string
     */
    public function getUriHash();

    /**
     * @param string $value
     * @return $this
     */
    public function setUriHash($value);

    /**
     * @return string
     */
    public function getCacheId();

    /**
     * @param string $value
     * @return $this
     */
    public function setCacheId($value);

    /**
     * @return string
     */
    public function getPageType();

    /**
     * @param string $value
     * @return $this
     */
    public function setPageType($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     * @return $this
     */
    public function setProductId($value);

    /**
     * @return int
     */
    public function getCategoryId();

    /**
     * @param int $value
     * @return $this
     */
    public function setCategoryId($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     * @return $this
     */
    public function setStoreId($value);

    /**
     * @return array
     */
    public function getVaryData();

    /**
     * @param string|array $value
     * @return $this
     */
    public function setVaryData($value);

    /**
     * @return string
     */
    public function getVaryString();

    /**
     * @param string $value
     * @return $this
     */
    public function setVaryDataHash($value);

    /**
     * @return string
     */
    public function getVaryDataHash();


    /**
     * @param string $value
     * @return $this
     */
    public function setCookie($value);

    /**
     * @return string
     */
    public function getCookie();

    /**
     * @param string $value
     * @return $this
     */
    public function setUserAgent($value);

    /**
     * @return string
     */
    public function getUserAgent();

    /**
     * @return int
     */
    public function getAttempts();

    /**
     * @param int $value
     * @return $this
     */
    public function setAttempts($value);

    /**
     * @return int
     */
    public function getPopularity();

    /**
     * @param int $value
     * @return $this
     */
    public function setPopularity($value);

    /**
     * @return string
     */
    public function getWarmRuleVersion();

    /**
     * @param string $value
     * @return $this
     */
    public function setWarmRuleVersion($value);

    /**
     * @return array
     */
    public function getWarmRuleIds();

    /**
     * @param array $value
     * @return $this
     */
    public function setWarmRuleIds(array $value);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param array $value
     * @return $this
     */
    public function setHeaders(array $value);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setStatus($value);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);


    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value);

    /**
     * @return string
     */
    public function getCachedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCachedAt($value);

    /**
     * @return string
     */
    public function getFlushedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setFlushedAt($value);

    /**
     * @return int
     */
    public function getSourceId();

    /**
     * @param int $value
     * @return $this
     */
    public function setSourceId($value);
}
