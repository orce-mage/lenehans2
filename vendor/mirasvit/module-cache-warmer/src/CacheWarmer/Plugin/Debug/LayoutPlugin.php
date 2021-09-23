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



namespace Mirasvit\CacheWarmer\Plugin\Debug;

use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Element;
use Mirasvit\CacheWarmer\Model\Config;

class LayoutPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * LayoutPlugin constructor.
     * @param Config $config
     * @param Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        Config $config,
        Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->config   = $config;
        $this->registry = $registry;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param Layout $layout
     * @param string $result
     * @return string
     */
    public function afterGetOutput(Layout $layout, $result)
    {
        if ($this->request->isAjax()) {
            return $result;
        }
        if ($header = $this->response->getHeader("Content-Type")) {
            if ($header->getFieldValue() != "" && $header->getFieldValue() != "text/html") {
                return $result;
            }
        }
        $nonCacheableBlocks = [];

        $paths = $layout->getUpdate()->asSimplexml()->xpath('//' . Element::TYPE_BLOCK . '[@cacheable="false"]');
        if (count($paths)) {
            foreach ($paths as $path) {
                $class = $path['class'];
                $name  = $path['name'];

                $nonCacheableBlocks[(string)$class] = (string)$name;
            }
        }

        $this->registry->register('NON_CACHEABLE_BLOCKS', count($nonCacheableBlocks), true);

        if ($this->config->isDebugToolbarEnabled() && trim($result) != "") {
            $result .= "<div class='mst-cache-warmer__ncb' data-ncb='" .
                base64_encode(\Zend_Json::encode($nonCacheableBlocks)) . "'></div>";
        }

        return $result;
    }
}
