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

interface TraceInterface
{
    const TABLE_NAME = 'mst_cache_warmer_trace';

    const ENTITY_TYPE_CACHE = 'cache';

    const ID          = 'trace_id';
    const ENTITY_TYPE = 'entity_type';
    const ENTITY_ID   = 'entity_id';
    const TRACE       = 'trace';
    const STARTED_AT  = 'started_at';
    const FINISHED_AT = 'finished_at';
    const CREATED_AT  = 'created_at';

    public function getId();

    /**
     * @return string
     */
    public function getEntityType();

    /**
     * @param string $value
     * @return $this
     */
    public function setEntityType($value);

    /**
     * @return string
     */
    public function getEntityId();

    /**
     * @param string $value
     * @return $this
     */
    public function setEntityId($value);

    /**
     * @return string|array
     */
    public function getTrace();

    /**
     * @param string|array $value
     * @return $this
     */
    public function setTrace($value);

    /**
     * @return string
     */
    public function getStartedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setStartedAt($value);

    /**
     * @return string
     */
    public function getFinishedAt();

    /**
     * @param string $value
     * @return $this
     */
    public function setFinishedAt($value);

    /**
     * @return string
     */
    public function getCreatedAt();
}