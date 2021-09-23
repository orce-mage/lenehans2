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



namespace Mirasvit\CacheWarmer\Service\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\CacheWarmer\Model\Config;

class DebugConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config 
     */
    private $moduleConfig;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->moduleConfig = $config;
    }

    /**
     * @return integer
     */
    public function isInfoBlockEnabled()
    {
        return $this->moduleConfig->isDebugToolbarEnabled();
    }

    /**
     * @return bool
     */
    public function isDebugAllowed()
    {
        $ips = $this->scopeConfig->getValue(
            'cache_warmer/debug/allowed_ip',
            ScopeInterface::SCOPE_STORE
        );

        if ($ips == '') {
            return true;
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $clientIp = $_SERVER['HTTP_X_REAL_IP'];
        } else {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        }

        $clientIps = explode(',', $clientIp); //we may have comma separated list.
        $clientIps = array_map('trim', $clientIps);

        $ips = explode(',', $ips);
        $ips = array_map('trim', $ips);
        $res = array_intersect($clientIps, $ips);

        return count($res) > 0;
    }
}
