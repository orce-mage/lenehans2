<?php
/**
 * MageBig AjaxInfiniteScroll - Magento 2 Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0),
 * available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @copyright Copyright (c) 2016 MageBig Inc. (http://www.magebig.io/)
 * @author Damian A. Pastorini (damian.pastorini@magebig.io)
 */

namespace MageBig\AjaxInfiniteScroll\Block;

use Magento\Framework\Serialize\Serializer\Json;

class Init extends \Magento\Framework\View\Element\Template
{

    protected $_serialize;
    private $scopeConfig;
    private $catalogSession;
    private $registry;

    /**
     * Init constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $registry
     * @param Json $serialize
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $registry,
        Json $serialize,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->catalogSession = $catalogSession;
        $this->registry = $registry;
        $this->_serialize = $serialize;
        parent::__construct($context, $data);
    }

    /**
     * @param $fullPath
     * @return mixed
     */
    public function getConfig($fullPath)
    {
        return $this->scopeConfig->getValue($fullPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isRedirectToCartEnabled()
    {
        return $this->getConfig('checkout/cart/redirect_to_cart');
    }

    /**
     * @param $node
     * @return mixed
     */
    public function getScrollConfig($node)
    {
        return $this->getConfig('magebig_ajaxinfinitescroll/' . $node);
    }

    /**
     * @param $selector
     * @return string
     */
    public function getSelector($selector)
    {
        return $this->getScrollConfig('selectors/'.$selector);
    }

    /**
     * @param $design
     * @return string
     */
    public function getDesign($design)
    {
        return $this->getScrollConfig('design/'.$design);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        $enabled = ($this->getScrollConfig('general/enabled') && $this->isEnabledInCurrentPage());
        return $enabled;
    }

    /**
     * @return mixed
     */
    public function isMemoryActive()
    {
        return $this->getScrollConfig('memory/enabled');
    }

    /**
     * @return mixed
     */
    public function getNextPageNumber()
    {
        return $this->getRequest()->getParam('p');
    }

    /**
     * @return string
     */
    public function getCurrentPageType()
    {
        $where = 'grid';
        $currentCategory = $this->getCurrentCategory();
        if ($currentCategory) {
            $where = "grid";
            if ($currentCategory->getIsAnchor()) {
                $where = "layer";
            }
        }
        $controller = $this->getRequest()->getControllerName();
        if ($controller == "result") {
            $where = "search";
        } elseif ($controller == "advanced") {
            $where = "advanced";
        }
        return $where;
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * check general and instance enable
     * @return bool
     */
    public function isEnabledInCurrentPage()
    {
        $pageType = $this->getCurrentPageType();
        return $this->getScrollConfig('instances/'.$pageType);
    }

    /**
     * @return bool|false
     */
    public function getLoaderImage()
    {
        $url = $this->getScrollConfig('design/loading_img');
        if (!empty($url)) {
            $url = strpos($url, 'http') === 0 ? $url : $this->getSkinUrl($url);
        }
        return empty($url) ? false : $url;
    }

    /**
     * @return string
     */
    public function getProductListMode()
    {
        // user mode
        $paramProductListMode = $this->getRequest()->getParam('product_list_mode');
        $currentMode = $paramProductListMode ? $paramProductListMode : $this->catalogSession->getDisplayMode();
        if ($currentMode) {
            switch ($currentMode) {
                case 'list':
                    $productListMode = 'list';
                    break;
                case 'grid':
                default:
                    $productListMode = 'grid';
            }
        } else {
            $defaultMode = $this->getConfig('catalog/frontend/list_mode');
            switch ($defaultMode) {
                case 'grid-list':
                    $productListMode = 'grid';
                    break;
                case 'list-grid':
                    $productListMode = 'list';
                    break;
                default:
                    $productListMode = $defaultMode;
            }
        }
        return $productListMode;
    }

    public function getJsonConfig() {
        $loadingText = $this->escapeHtml(__($this->getScrollConfig('design/loading_text')));
        $text = $this->escapeHtml(__($this->getDesign('done_text')));
        $triggerText = $this->escapeHtml(__($this->getDesign('load_more_text')));
        $triggerTextPrev = $this->escapeHtml(__($this->getDesign('load_more_text_prev')));

        $config = [
            'isRedirectCart' => (int)$this->isRedirectToCartEnabled(),
            'negativeMargin' => (int)$this->getDesign('buffer_px'),
            'text' => $text,
            'spinnerText' => $loadingText,
            "trigger" => [
                "text" => $triggerText,
                "textPrev" => $triggerTextPrev,
                "offset" => (int)$this->getDesign('load_more_threshold')
            ],
            "memoryActive" => $this->isMemoryActive() ? 1 : 0
        ];

        return $this->_serialize->serialize($config);
    }
}
