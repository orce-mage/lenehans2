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



namespace Mirasvit\CacheWarmer\Api\Repository;

use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

interface WarmRuleRepositoryInterface
{
    /**
     * @return \Mirasvit\CacheWarmer\Model\ResourceModel\WarmRule\Collection|WarmRuleInterface[]
     */
    public function getCollection();

    /**
     * @return WarmRuleInterface
     */
    public function create();

    /**
     * @param WarmRuleInterface $model
     * @return WarmRuleInterface
     */
    public function save(WarmRuleInterface $model);

    /**
     * @param int $id
     * @return WarmRuleInterface|false
     */
    public function get($id);

    /**
     * @param WarmRuleInterface $model
     * @return bool
     */
    public function delete(WarmRuleInterface $model);
}