<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class CssInliner
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class CssInliner
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Fonts
     */
    protected $_fontHelper;

    /**
     * var \WeltPixel\CustomHeader\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CssInliner constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Fonts $fontHelper
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Fonts $fontHelper,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_fontHelper = $fontHelper;
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Css\PreProcessor\Adapter\CssInliner $subject
     * @param $css
     * @return array
     */
    public function beforeSetCss(\Magento\Framework\Css\PreProcessor\Adapter\CssInliner $subject, $css)
    {
        if(!$this->_helper->isEnabled($this->_storeManager->getStore()->getId())) {
            return [$css];
        }

        $newCss = $css . $this->_generateCssStr();

        return [$newCss];
    }

    /**
     * @return string
     */
    protected function _generateCssStr() {

        $content = '';
        $storeId = $this->_storeManager->getStore()->getId();

        $bodyWrapperBgColor = $this->_helper->getBodyWrapperBackgroundColor();
        $headerBgColor = $this->_helper->getHeaderBackgroundColor();
        $footerBgColor = $this->_helper->getFooterBackgroundColor();
        $orderTotalBgColor = $this->_helper->getBodyOrderTotalBackgroundColor();

        $bodyBackgroundColor = $this->_helper->getBodyBackgroundColor($storeId);
        $bodyFontColor = $this->_helper->getBodyFontColor($storeId);
        $bodyLinkColor = $this->_helper->getBodyLinkColor($storeId);
        $topmenuFontColor = $this->_helper->getTopmenuFontColor($storeId);
        $topmenuPaddingTopBottom = $this->_helper->getTopmenuPaddingTopBottom($storeId);
        $topmenuBgColor = $this->_helper->getTopmenuBgColor($storeId);
        $topmenuPadding = $this->_helper->getTopmenuPadding($storeId);

        $headingOneFontColor = $this->_helper->getHeadingOneFontColor($storeId);
        $headingOneUseGoogleFonts = $this->_helper->useHeadingOneGoogleFonts($storeId);
        $headingOneWebsafeFonts = $this->_helper->getHeadingOneWebsafeFont($storeId);
        $headingOneFallbackFonts = $this->_helper->getHeadingOneFallbackFont($storeId);
        $headingOneFontFamily = $this->_helper->getHeadingOneFontFamily($storeId);
        $headingOneFontWeight = $this->_helper->getHeadingOneFontWeight($storeId);
        $headingOneFontSize = $this->_helper->getHeadingOneFontSize($storeId);
        $headingOneFontStyle = $this->_helper->getHeadingOneFontStyle($storeId);
        $headingOneFontHeight = $this->_helper->getHeadingOneFontHeight($storeId);
        $headingOneLatterSpacing = $this->_helper->getHeadingOneLetterSpacing($storeId);
        $hOneFontfamily = $this->_getFontfamily($headingOneUseGoogleFonts,$headingOneWebsafeFonts,$headingOneFallbackFonts,$headingOneFontFamily);

        $headingTwooFontColor = $this->_helper->getHeadingTwooFontColor($storeId);
        $headingTwooUseGoogleFonts = $this->_helper->useHeadingTwooGoogleFonts($storeId);
        $headingTwooWebsafeFonts = $this->_helper->getHeadingTwooWebsafeFont($storeId);
        $headingTwooFallbackFonts = $this->_helper->getHeadingTwooFallbackFont($storeId);
        $headingTwooFontFamily = $this->_helper->getHeadingTwooFontFamily($storeId);
        $headingTwooFontWeight = $this->_helper->getHeadingTwooFontWeight($storeId);
        $headingTwooFontSize = $this->_helper->getHeadingTwooFontSize($storeId);
        $headingTwooFontStyle = $this->_helper->getHeadingTwooFontStyle($storeId);
        $headingTwooFontHeight = $this->_helper->getHeadingTwooFontHeight($storeId);
        $headingTwooLatterSpacing = $this->_helper->getHeadingTwooLetterSpacing($storeId);
        $hTwoFontfamily = $this->_getFontfamily($headingTwooUseGoogleFonts, $headingTwooWebsafeFonts, $headingTwooFallbackFonts, $headingTwooFontFamily);

        $headingThreeFontColor = $this->_helper->getHeadingThreeFontColor($storeId);
        $headingThreeUseGoogleFonts = $this->_helper->useHeadingThreeGoogleFonts($storeId);
        $headingThreeWebsafeFonts = $this->_helper->getHeadingThreeWebsafeFont($storeId);
        $headingThreeFallbackFonts = $this->_helper->getHeadingThreeFallbackFont($storeId);
        $headingThreeFontFamily = $this->_helper->getHeadingThreeFontFamily($storeId);
        $headingThreeFontWeight = $this->_helper->getHeadingThreeFontWeight($storeId);
        $headingThreeFontSize = $this->_helper->getHeadingThreeFontSize($storeId);
        $headingThreeFontStyle = $this->_helper->getHeadingThreeFontStyle($storeId);
        $headingThreeFontHeight = $this->_helper->getHeadingThreeFontHeight($storeId);
        $headingThreeLatterSpacing = $this->_helper->getHeadingThreeLetterSpacing($storeId);
        $hThreeFontfamily = $this->_getFontfamily($headingThreeUseGoogleFonts, $headingThreeWebsafeFonts, $headingThreeFallbackFonts, $headingThreeFontFamily);

        $pFontColor = $this->_helper->getPFontColor($storeId);
        $pUseGoogleFonts = $this->_helper->usePGoogleFonts($storeId);
        $pWebsafeFonts = $this->_helper->getPWebsafeFont($storeId);
        $pFallbackFonts = $this->_helper->getPFallbackFont($storeId);
        $pFontFamily = $this->_helper->getPFontFamily($storeId);
        $pFontWeight = $this->_helper->getPFontWeight($storeId);
        $pFontSize = $this->_helper->getPFontSize($storeId);
        $pFontStyle = $this->_helper->getPFontStyle($storeId);
        $pFontHeight = $this->_helper->getPFontHeight($storeId);
        $pLatterSpacing = $this->_helper->getPLetterSpacing($storeId);
        $paragraphFontFamily = $this->_getFontfamily($pUseGoogleFonts, $pWebsafeFonts, $pFallbackFonts, $pFontFamily);

        $buttonFontColor = $this->_helper->getButtonFontColor($storeId);
        $buttonBgColor = $this->_helper->getButtonBgColor($storeId);
        $buttonBorderColor = $this->_helper->getButtonBorderColor($storeId);
        $buttonHoverFontColor = $this->_helper->getButtonHoverFontColor($storeId);
        $buttonHoverBgColor = $this->_helper->getButtonHoverBgColor($storeId);
        $buttonHoverBorderColor = $this->_helper->getButtonHoverBorderColor($storeId);

        $commentFontColor = $this->_helper->getCommentFontColor($storeId);
        $commentBgColor = $this->_helper->getCommentBgColor($storeId);



        //Generate Css
        $content .= "
td.wrapper-inner {
    background-color: $bodyWrapperBgColor !important;
}

td.header {
    background-color: $headerBgColor !important;
}

td.footer {
    background-color: $footerBgColor !important;
}

tfoot.order-totals tr th, tfoot.order-totals td {
    background-color: $orderTotalBgColor !important;
}
td.main-content {
    background-color: $bodyBackgroundColor;
    color: $bodyFontColor;
}

a {
    color: $bodyLinkColor;
    text-decoration: underline;
    cursor: pointer;
}

a:visited {
    color: $bodyLinkColor !important;
    text-decoration: none;
    cursor: pointer;
}
nav.navigation a, nav.navigation a h3{
    color: $topmenuFontColor;
}
nav.navigation a:visited, nav.navigation a:visited h3{
    color: $topmenuFontColor !important;
}
nav.navigation ul {
    text-align: center;
    padding: $topmenuPaddingTopBottom  0;
    margin: 0 !important;
    background: $topmenuBgColor;
}
nav.navigation ul li {
    display: inline;
    padding: 0  $topmenuPadding;
    line-height: 2.2;
}

nav.navigation ul li a{
    text-decoration: none;
}


h1 {
    color: $headingOneFontColor !important;
    font-family: $hOneFontfamily;
    font-weight: $headingOneFontWeight;
    font-size: $headingOneFontSize;
    font-style: $headingOneFontStyle;
    line-height: $headingOneFontHeight;
    letter-spacing: $headingOneLatterSpacing;
}
h2 {
    color: $headingTwooFontColor !important;
    font-family: $hTwoFontfamily;
    font-weight: $headingTwooFontWeight;
    font-size: $headingTwooFontSize;
    font-style: $headingTwooFontStyle;
    line-height: $headingTwooFontHeight;
    letter-spacing: $headingTwooLatterSpacing;
}
h3, table.shipment-track th {
    color: $headingThreeFontColor !important;
    font-family: $hThreeFontfamily;
    font-weight: $headingThreeFontWeight;
    font-size: $headingThreeFontSize;
    font-style: $headingThreeFontStyle;
    line-height: $headingThreeFontHeight;
    letter-spacing: $headingThreeLatterSpacing;
}
p, table.shipment-track td {
    color: $pFontColor !important;
    font-family: $paragraphFontFamily;
    font-weight: $pFontWeight;
    font-size: $pFontSize;
    font-style: $pFontStyle;
    line-height: $pFontHeight;
    letter-spacing: $pLatterSpacing;
}

.dark h1, .dark h2, .dark h3, .dark p {
    color: #fff
}

.light h1, .light h2, .light h3, .light p {
    color: #000
}

button {
    color: $buttonFontColor;
    background-color: $buttonBgColor;
    border-color: $buttonBorderColor;
    cursor: pointer;
}
.button .inner-wrapper td {
    background-color: $buttonBgColor !important;
}
.button .inner-wrapper td a {
    border: 1px solid $buttonBorderColor !important;
    color: $buttonFontColor !important;
}

.button .inner-wrapper td:hover {
    background-color: $buttonHoverBgColor !important;
}

.button .inner-wrapper td:hover a {
    border: 1px solid $buttonHoverBorderColor !important;
    color: $buttonHoverFontColor !important;
}

.wp-method-info .payment-method .title {
    color: #555656;
    font-family: $paragraphFontFamily;
    font-weight: $pFontWeight;
    font-size: $pFontSize;
    font-style: $pFontStyle;
    line-height: $pFontHeight;
    letter-spacing: $pLatterSpacing;
}

table.message-info td{
    color: $commentFontColor;
    background-color: $commentBgColor;
    font-family: $paragraphFontFamily;
    font-weight: $pFontWeight;
    font-size: $pFontSize;
    font-style: $pFontStyle;
    line-height: $pFontHeight;
    letter-spacing: $pLatterSpacing;

}

table.shipment-track th {
    text-transform: uppercase;
}

table.shipment-track th,
table.shipment-track td {
    background-color: #fff !important;
}
table.order-details td {
    width: 40% !important;
}
@media only screen and (max-width: 479px) {

  .order-details .address-details,
  .order-details .method-info {
    display: table-cell !important;
    padding: 10px 0 !important;
    width: auto !important;
  }

}

.wp-products-grid .price-box .price-label {display:none;}
.wp-products-grid .price-box .old-price .price {text-decoration: line-through;}
.wp-products-grid .price-box span {font-size: 14px;}
";
        return $content;
    }

    /**
     * @param $useGoogleFonts
     * @param $websafeFonts
     * @param $fallbackFonts
     * @param $fontFamily
     * @return string
     */
    protected function _getFontfamily($useGoogleFonts, $websafeFonts, $fallbackFonts, $fontFamily)
    {
        if($useGoogleFonts) {
            return  $fontFamily .', '. $this->sanitizeFontStr($fallbackFonts);
        } else {
            return $this->sanitizeFontStr($websafeFonts);
        }
    }

    /**
     * Build font family str
     * replace '+' with ' '
     * replace '_' with ', '
     *
     * @param $str
     * @return null|string|string[]
     */
    private function sanitizeFontStr($str) {
        $patternPlus = '/\+/';
        $replacement = ' ';
        $removePlus = preg_replace($patternPlus, $replacement, $str);

        $patternUs = '/\_/';
        $replacement = ', ';
        $removeUs = preg_replace($patternUs, $replacement, $removePlus);

        return $removeUs;
    }

}
