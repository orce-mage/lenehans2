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

interface LogInterface
{
    const TABLE_NAME = 'mst_cache_warmer_log';

    const ID            = 'log_id';
    const RESPONSE_TIME = 'response_time';
    const IS_HIT        = 'is_hit';
    const URI           = 'uri';
    const CREATED_AT    = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return float
     */
    public function getResponseTime();

    /**
     * @param float $value
     * @return $this
     */
    public function setResponseTime($value);

    /**
     * @return bool
     */
    public function isHit();

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsHit($value);

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
    public function getCreatedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value);
}