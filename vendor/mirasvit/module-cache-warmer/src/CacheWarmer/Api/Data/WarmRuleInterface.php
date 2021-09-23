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

interface WarmRuleInterface
{
    const TABLE_NAME = 'mst_cache_warmer_warm_rule';

    const ID        = 'warm_rule_id';
    const NAME      = 'name';
    const IS_ACTIVE = 'is_active';
    const PRIORITY  = 'priority';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    const HEADERS_SERIALIZED   = 'headers_serialized';
    const VARY_DATA_SERIALIZED = 'vary_data_serialized';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     * @return $this
     */
    public function setName($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsActive($value);

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
    public function getConditionsSerialized();

    /**
     * @param string $value
     * @return $this
     */
    public function setConditionsSerialized($value);

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
     * @return array
     */
    public function getVaryData();

    /**
     * @param array $value
     * @return $this
     */
    public function setVaryData(array $value);

    /**
     * @return \Mirasvit\CacheWarmer\Model\WarmRule\Rule
     */
    public function getRule();
}