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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;

interface PageServiceInterface
{
    const PRODUCT_REG  = 'm__warm_current_product_id';
    const CATEGORY_REG = 'm__warm_current_category_id';

    /**
     * @param PageInterface $page
     * @return bool
     */
    public function isCached(PageInterface $page);

    /**
     * Adds URL of the page to the warmer queue.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function collect(RequestInterface $request, ResponseInterface $response);

    /**
     * @param array|string $varyData
     * @return array|string
     */
    public function prepareVaryData($varyData);

    /**
     * Check if URL is valid
     * @param string $url
     * @return bool
     */
    public function isValidUrl($url);

    /**
     * @param PageInterface $page
     * @return void
     */
    public function setUncacheableStatus(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return void
     */
    public function setPendingStatus(PageInterface $page);

    /**
     * @param PageInterface $page
     * @return void
     */
    public function setCachedStatus(PageInterface $page);
}