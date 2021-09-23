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



namespace Mirasvit\CacheWarmer\Service\Warmer;

use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Service\Curl\CurlResponse;

class PageWarmStatus
{
    /**
     * @var PageInterface
     */
    private $page;

    /**
     * @var CurlResponse
     */
    private $response;

    /**
     * PageWarmStatus constructor.
     * @param PageInterface $page
     * @param CurlResponse $response
     */
    public function __construct(PageInterface $page, CurlResponse $response)
    {
        $this->page     = $page;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return '#' . $this->page->getId() . ' ' . $this->response->getCode() . ' ' . $this->page->getUri();
    }

    /**
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->response->getCode() !== 200;
    }

    /**
     * @return bool
     */
    public function isSoftError()
    {
        return in_array($this->response->getCode(), [404, 301, 302]);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->response->getCode();
    }
}
