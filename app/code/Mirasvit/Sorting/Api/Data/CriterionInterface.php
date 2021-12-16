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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Api\Data;

use Mirasvit\Sorting\Model\Criterion\ConditionCluster;

interface CriterionInterface
{
    const SORT_BY_ATTRIBUTE      = 'attribute';
    const SORT_BY_RANKING_FACTOR = 'ranking_factor';

    const TABLE_NAME = 'mst_sorting_criterion';

    const ID = 'criterion_id';

    const NAME                  = 'name';
    const CODE                  = 'code';
    const IS_ACTIVE             = 'is_active';
    const IS_DEFAULT            = 'is_default';
    const IS_SEARCH_DEFAULT     = 'is_search_default';
    const POSITION              = 'position';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const CONDITIONS            = 'conditions';

    const CONDITION_SORT_BY        = 'sortBy';
    const CONDITION_ATTRIBUTE      = 'attribute';
    const CONDITION_RANKING_FACTOR = 'rankingFactor';
    const CONDITION_DIRECTION      = 'direction';
    const CONDITION_WEIGHT         = 'weight';

    const CONDITION_SORT_BY_ATTRIBUTE      = 'attribute';
    const CONDITION_SORT_BY_RANKING_FACTOR = 'ranking_factor';


    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCode($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return bool
     */
    public function isDefault();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsDefault($value);

    /**
     * @return bool
     */
    public function isSearchDefault();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsSearchDefault($value);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPosition($value);

    /**
     * @return ConditionCluster
     */
    public function getConditionCluster();

    /**
     * @param ConditionCluster $value
     *
     * @return $this
     */
    public function setConditionCluster(ConditionCluster $value);
}
