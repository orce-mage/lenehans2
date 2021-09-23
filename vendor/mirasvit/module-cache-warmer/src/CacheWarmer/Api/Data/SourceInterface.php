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


interface SourceInterface
{
    const TABLE_NAME = 'mst_cache_warmer_source';

    const ID              = 'source_id';
    const SOURCE_NAME     = 'source_name';
    const SOURCE_TYPE     = 'source_type';
    const PATH            = 'path';
    const IS_ACTIVE       = 'is_active';
    const LAST_SYNC_AT    = 'last_sync_at';
    const CUSTOMER_GROUPS = 'customer_groups';

    const TYPE_VISITOR = 0;
    const TYPE_CRAWLER = 1;
    const TYPE_SITEMAP = 2;
    const TYPE_FILE    = 3;

    const DEFAULT_SOURCE_ID = 1;

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getSourceName();

    /**
     * @param string $value
     * @return $this
     */
    public function setSourceName($value);

    /**
     * @return string
     */
    public function getSourceType();

    /**
     * @param string $value
     * @return $this
     */
    public function setSourceType($value);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $value
     * @return $this
     */
    public function setPath($value);

    /**
     * @return bool
     */
    public function getIsActive();

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return string
     */
    public function getLastSyncronizedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setLastSyncronizedAt($value);

    /**
     * @return array
     */
    public function getCustomerGroups();

    /**
     * @param array $value
     * @return $this
     */
    public function setCustomerGroups($value);
}
