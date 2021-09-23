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

interface JobInterface
{
    const PRIORITY_NORMAL    = 1;
    const PRIORITY_EMERGENCY = 2;

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_RUNNING   = 'running';
    const STATUS_ERROR     = 'error';
    const STATUS_MISSED    = 'missed';
    const STATUS_COMPLETED = 'completed';

    const TABLE_NAME = 'mst_cache_warmer_job';

    const ID                = 'job_id';
    const PRIORITY          = 'priority';
    const FILTER_SERIALIZED = 'filter_serialized';
    const INFO_SERIALIZED   = 'info_serialized';
    const TRACE_SERIALIZED  = 'trace_serialized';
    const STATUS            = 'status';
    const STARTED_AT        = 'started_at';
    const FINISHED_AT       = 'finished_at';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @param int $value
     * @return $this
     */
    public function setPriority($value);

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
     * @return array
     */
    public function getFilter();

    /**
     * @param array $value
     * @return $this
     */
    public function setFilter($value);

    /**
     * @return array
     */
    public function getInfo();

    /**
     * @param array $value
     * @return $this
     */
    public function setInfo($value);

    /**
     * @return array
     */
    public function getTrace();

    /**
     * @param array $value
     * @return $this
     */
    public function setTrace($value);

    /**
     * @return int
     */
    public function getStartedAt();

    /**
     * @param int $value
     * @return $this
     */
    public function setStartedAt($value);

    /**
     * @return int
     */
    public function getFinishedAt();

    /**
     * @param int $value
     * @return $this
     */
    public function setFinishedAt($value);

    /**
     * @return int
     */
    public function getCreatedAt();

    /**
     * @param int $value
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return int
     */
    public function getUpdatedAt();

    /**
     * @param int $value
     * @return $this
     */
    public function setUpdatedAt($value);
}
