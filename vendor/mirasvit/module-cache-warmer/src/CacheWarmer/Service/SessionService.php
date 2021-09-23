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



namespace Mirasvit\CacheWarmer\Service;

use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Service\Curl\CurlResponse;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Helper\Serializer;

class SessionService implements SessionServiceInterface
{
    /**
     * @var null|string $userAgentFirstPart
     */
    private static $userAgentFirstPart = null;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * SessionService constructor.
     * @param Config $config
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param Serializer $serializer
     */
    public function __construct(
        Config $config,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        Serializer $serializer
    ) {
        $this->config = $config;
        $this->cookieManager = $cookieManager;
        $this->serializer = $serializer;
    }


    /**
     * @param array $varyData
     * @param int   $productId
     * @param int   $categoryId
     * @return string
     */
    public function getSessionCookie($varyData, $productId, $categoryId)
    {
        $agent = "";
        $agent .= $this->getUserAgentFirstPart();
        $agent .= base64_encode($this->serializer->serialize($varyData));
        $agent .= SessionServiceInterface::PRODUCT_BEGIN_TAG
            . $productId . SessionServiceInterface::PRODUCT_END_TAG;
        $agent .= SessionServiceInterface::CATEGORY_BEGIN_TAG
            . $categoryId . SessionServiceInterface::CATEGORY_END_TAG;

        return $agent;
    }

    /**
     * @return null|string
     */
    private function getUserAgentFirstPart()
    {
        if (self::$userAgentFirstPart === null) {
            self::$userAgentFirstPart = WarmerServiceInterface::USER_AGENT . $this->getWarmerUniquePart() . ':';
        }

        return self::$userAgentFirstPart;
    }

    /**
     * Create unique warmer user agent for security reason
     * @return string
     */
    private function getWarmerUniquePart()
    {
        return $this->config->getWarmerUniquePart();
    }


    /**
     * @return string|null
     */
    public function getSessionDataFromCookie()
    {
        return $this->cookieManager->getCookie(SessionServiceInterface::SESSION_COOKIE);
    }

    /**
     * We use this session data to restore enviroment during warming
     *
     * @return bool|array
     */
    public function getSessionData()
    {
        $agent = $this->getSessionDataFromCookie();
        if (strpos($agent, $this->getUserAgentFirstPart()) !== false) {
            $data = substr($agent, strpos($agent, $this->getUserAgentFirstPart()) + strlen($this->getUserAgentFirstPart()));
            $data = preg_replace('/' . SessionServiceInterface::PRODUCT_BEGIN_TAG
                . '(.*?)' . SessionServiceInterface::PRODUCT_END_TAG . '/ims', '', $data);
            $data = preg_replace('/' . SessionServiceInterface::CATEGORY_BEGIN_TAG
                . '(.*?)' . SessionServiceInterface::CATEGORY_END_TAG . '/ims', '', $data);
            $data = $this->serializer->unserialize(base64_decode($data));

            return $data;
        }

        return false;
    }

    /**
     * @return bool|array
     */
    public function getProductId()
    {
        $agent = $this->getSessionDataFromCookie();
        if (strpos($agent, $this->getUserAgentFirstPart()) !== false) {
            preg_match('/' . SessionServiceInterface::PRODUCT_BEGIN_TAG
                . '(.*?)' . SessionServiceInterface::PRODUCT_END_TAG . '/ims', $agent, $data);

            return (isset($data[1])) ? $data[1] : false;
        }

        return false;
    }

    /**
     * @return bool|array
     */
    public function getCategoryId()
    {
        $agent = $this->getSessionDataFromCookie();
        if (strpos($agent, $this->getUserAgentFirstPart()) !== false) {
            preg_match('/' . SessionServiceInterface::CATEGORY_BEGIN_TAG
                . '(.*?)' . SessionServiceInterface::CATEGORY_END_TAG . '/ims', $agent, $data);

            return (isset($data[1])) ? $data[1] : false;
        }

        return false;
    }


    /**
     * @param PageInterface $page
     * @return array
     */
    public function getCookies(PageInterface $page)
    {
        $cookies = [];
        if ($page->getCookie()) {
            $cooks = explode(";", $page->getCookie());
            foreach ($cooks as $c) {
                $cc = explode("=", $c);
                $cookies[$cc[0]] = $cc[1];
            }
        } elseif ($page->getVaryString()) {
            $cookies['X-Magento-Vary'] = $page->getVaryString();
        }

        $sessionCookie = $this->getSessionCookie(
            $page->getVaryData(),
            $page->getProductId(),
            $page->getCategoryId()
        );
        $cookies[SessionServiceInterface::SESSION_COOKIE] = $sessionCookie;
        //we add this cookie to have correct cache hit stats
        $cookies['mst-cache-warmer-track'] = 1;
        return $cookies;
    }
}
