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



namespace Mirasvit\CacheWarmer\Api\Service;

use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Model\ResourceModel\Page\Collection;
use Mirasvit\CacheWarmer\Service\Warmer\PageWarmStatus;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

interface WarmerServiceInterface
{
    const USER_AGENT      = 'CacheWarmer';
    const STATUS_COOKIE   = 'CacheWarmerStatus';
    const WARMER_UNIQUE_VALUE = 'cache_warmer/unique_value';

    /**
     * @param Collection $collection
     * @param WarmRuleInterface $rule
     * @return PageWarmStatus[]
     */
    public function warmCollection(Collection $collection, WarmRuleInterface $rule = null);

    /**
     * @param PageInterface $page
     * @return bool
     */
    public function cleanPage(PageInterface $page);
}
