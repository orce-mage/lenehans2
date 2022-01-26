<?php
/**
 * Copyright © 2016 MageBig . All rights reserved.
 */

namespace MageBig\MbFrame\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $pageResult;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Result\PageFactory         $pageResult
     * @param \Magento\Framework\View\Page\Config                $pageConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $pageResult,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig  = $scopeConfig;
        $this->pageResult    = $pageResult;
        $this->pageConfig    = $pageConfig;
    }

    /**
     * @param $fullPath
     * @return mixed
     */
    public function getConfig($fullPath)
    {
        return $this->_scopeConfig->getValue($fullPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return mixed
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getPageLayout()
    {
        if ($this->pageConfig->getPageLayout()) {
            return $this->pageConfig->getPageLayout();
        } else {
            return '2columns-left';
        }
    }

    /**
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->_getUrl('', ['_current' => true]);
        $urlRewrite = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);

        return $currentUrl == $urlRewrite;
    }
    // public function isHomePage()
    // {
    //     $currentUrl = $this->_getUrl('', ['_current' => true]);
    //     $curParse = parse_url($currentUrl);
    //     $curPath = $curParse['path'];
    //
    //     $urlRewrite = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    //     $urlParse = parse_url($urlRewrite);
    //     $urlPath = $urlParse['path'];
    //
    //     $isHome = false;
    //     if (strlen($curPath) > 1 && strpos($urlPath, $curPath) && strpos('quickview', $curPath) !== false) {
    //         $isHome = true;
    //     }
    //
    //     return $currentUrl == $urlRewrite || $isHome;
    // }

    /**
     * @return string
     */
    public function getPageColumn()
    {
        $layout_column = $this->getPageLayout();
        if ($layout_column == '1column') {
            $viewport_1600 = $this->getConfig('mbconfig/category_view/category_one_column/viewport_1600');
            $viewport_1200 = $this->getConfig('mbconfig/category_view/category_one_column/viewport_1200');
            $viewport_992  = $this->getConfig('mbconfig/category_view/category_one_column/viewport_992');
            $viewport_768  = $this->getConfig('mbconfig/category_view/category_one_column/viewport_768');
            $viewport_576  = $this->getConfig('mbconfig/category_view/category_one_column/viewport_576');
            $viewport_0    = $this->getConfig('mbconfig/category_view/category_one_column/viewport_0');
        } elseif ($layout_column == '3columns') {
            $viewport_1600 = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_1600');
            $viewport_1200 = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_1200');
            $viewport_992  = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_992');
            $viewport_768  = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_768');
            $viewport_576  = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_576');
            $viewport_0    = $this->getConfig('mbconfig/category_view/category_three_columns/viewport_0');
        } else {
            $viewport_1600 = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_1600');
            $viewport_1200 = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_1200');
            $viewport_992  = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_992');
            $viewport_768  = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_768');
            $viewport_576  = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_576');
            $viewport_0    = $this->getConfig('mbconfig/category_view/category_two_columns/viewport_0');
        }
        $col = [];
        if ($this->getConfig('mbconfig/general/full_width')) {
            switch ($viewport_1600) {
                case 2:
                    $col[] = 'tp-2-col';
                    break;
                case 3:
                    $col[] = 'tp-3-col';
                    break;
                case 4:
                    $col[] = 'tp-4-col';
                    break;
                case 5:
                    $col[] = 'tp-5-col';
                    break;
                case 6:
                    $col[] = 'tp-6-col';
                    break;

                default:
                    $col[] = '';
                    break;
            }
        }
        switch ($viewport_1200) {
            case 2:
                $col[] = 'col-xl-6';
                break;
            case 3:
                $col[] = 'col-xl-4';
                break;
            case 4:
                $col[] = 'col-xl-3';
                break;
            case 5:
                $col[] = 'tp-xl-5-col';
                break;
            case 6:
                $col[] = 'col-xl-2';
                break;

            default:
                $col[] = '';
                break;
        }
        switch ($viewport_992) {
            case 2:
                $col[] = 'col-lg-6';
                break;
            case 3:
                $col[] = 'col-lg-4';
                break;
            case 4:
                $col[] = 'col-lg-3';
                break;
            case 5:
                $col[] = 'tp-lg-5-col';
                break;
            case 6:
                $col[] = 'col-lg-2';
                break;

            default:
                $col[] = '';
                break;
        }
        switch ($viewport_768) {
            case 2:
                $col[] = 'col-md-6';
                break;
            case 3:
                $col[] = 'col-md-4';
                break;
            case 4:
                $col[] = 'col-md-3';
                break;
            case 5:
                $col[] = 'tp-md-5-col';
                break;
            case 6:
                $col[] = 'col-md-2';
                break;

            default:
                $col[] = '';
                break;
        }
        switch ($viewport_576) {
            case 2:
                $col[] = 'col-sm-6';
                break;
            case 3:
                $col[] = 'col-sm-4';
                break;
            case 4:
                $col[] = 'col-sm-3';
                break;

            default:
                $col[] = '';
                break;
        }
        switch ($viewport_0) {
            case 1:
                $col[] = 'col-12';
                break;
            case 2:
                $col[] = 'col-6';
                break;
            case 3:
                $col[] = 'col-4';
                break;

            default:
                $col[] = '';
                break;
        }

        $cols = implode(' ', $col);

        return $cols;
    }

    public function isMobile()
    {
        $regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|"
            . 'htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|'
            . "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|"
            . "symbian|smartphone|mmp|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|"
            . "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220"
            . ')/i';

        if (preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            return true;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = [
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'oper', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-', ];

        if (in_array($mobile_ua, $mobile_agents)) {
            return true;
        }

        if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']), 'OperaMini') > 0) {
            return true;
        }

        return false;
    }

    public function getCategoryStore($product)
    {
        $html = '';
        if ($this->getConfig('mbconfig/product_view/catalog_list')) {
            $categoryCollection = $product->getCategoryCollection()->addNameToResult()
                ->addAttributeToFilter('is_active', 1)
                ->setStore($this->_storeManager->getStore());
            $catLink = [];

            foreach ($categoryCollection as $category) {
                $catLink[] = '<a href="' . $category->getUrl() . '">' . $category->getName() . '</a>';
            }
            $countCat = count($catLink);

            if ($countCat > 1) {
                $catLabel = __('Categories');
            } else {
                $catLabel = __('Category');
            }

            if ($countCat) {
                $html = '<div class="cat-links"><span>' . $catLabel . ': </span><span>' . implode(', ', $catLink) . '</span></div>';
            }
        }

        return $html;
    }
}
