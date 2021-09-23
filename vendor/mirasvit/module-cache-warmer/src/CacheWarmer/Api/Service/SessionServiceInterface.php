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

interface SessionServiceInterface
{
    const SESSION_COOKIE   = 'CacheWarmer';

    const PRODUCT_BEGIN_TAG  = 'prod_id_begin_';
    const PRODUCT_END_TAG    = '_prod_id_end';
    const CATEGORY_BEGIN_TAG = 'cat_id_begin_';
    const CATEGORY_END_TAG   = '_cat_id_end';

    /**
     * @return bool|array
     */
    public function getSessionData();

    /**
     * @return bool|array
     */
    public function getProductId();

    /**
     * @return bool|array
     */
    public function getCategoryId();

    /**
     * @param PageInterface $page
     * @return array
     */
    public function getCookies(PageInterface $page);
}
