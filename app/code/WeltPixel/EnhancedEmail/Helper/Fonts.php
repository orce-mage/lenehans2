<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Helper;
/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Fonts extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $_googlefontUrl = 'https://fonts.googleapis.com/css?display=swap&family=';

    /**
     * @var array
     */
    protected $_fontFamilyOptions = [
        'h1__font____family',
        'h2__font____family',
        'h3__font____family',
        'paragraph__font____family',

    ];

    /**
     * @var array
     */
    protected $avilableFontFamilys = [
        [
            'font' => 'h1/h1__font____family',
            'weight' => 'h1/h1__font____weight',
            'characterset' => 'h1/h1__font____family_characterset'
        ],
        [
            'font' => 'h2/h2__font____family',
            'weight' => 'h2/h2__font____weight',
            'characterset' => 'h2/h2__font____family_characterset'
        ],
        [
            'font' => 'h3/h3__font____family',
            'weight' => 'h3/h3__font____weight',
            'characterset' => 'h3/h3__font____family_characterset'
        ],
        [
            'font' => 'paragraph/paragraph__font____family',
            'weight' => 'paragraph/paragraph__font____weight',
            'characterset' => 'paragraph/paragraph__font____family_characterset'
        ]

    ];

    /**
     * @return array
     */
    public function getFontFamilyOptions()
    {
        return $this->_fontFamilyOptions;
    }

    /**
     * @return bool|string
     */
    public function getGoogleFonts()
    {
        $baseUrl = $this->_googlefontUrl;

        $fontUrl = $this->_getFontFamilyMergedUrl();

        if (strlen(trim($fontUrl))) {
            return $baseUrl . $fontUrl;
        }

        return false;
    }

    /**
     *Gets all the font options from the backend and it will construct the final font url
     * @return boolean|string
     */
    private function _getFontFamilyMergedUrl()
    {
        $fontsArray = [];
        foreach ($this->avilableFontFamilys as $availableFamily) {
            $fontFamily = str_replace(' ', '+', $this->scopeConfig->getValue('weltpixel_enhancedemail/' . $availableFamily['font'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if ($fontFamily) {
                $fontWeight = $this->scopeConfig->getValue('weltpixel_enhancedemail/' . $availableFamily['weight'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $fontCharacterset = $this->scopeConfig->getValue('weltpixel_enhancedemail/' . $availableFamily['characterset'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($fontWeight) {
                    $fontsArray[$fontFamily][] = array_map('trim', explode(',', $fontWeight));
                }
                if ($fontCharacterset) {
                    $fontsArray['_characterset'][] = explode(',', $fontCharacterset);
                }

            }
        }

        return $this->_buildUrl($fontsArray);
    }

    /**
     * Normalizes the admin options and constructs the final url into one merged font url
     * @param array $fontsArray
     * @return boolean|string
     */
    private function _buildUrl($fontsArray)
    {
        if (empty($fontsArray)) {
            return false;
        }

        $normalizedFontOptions = array();
        $subset = '';

        foreach ($fontsArray as $fontKey => $fontOptions) {
            $tmpArray = array();
            foreach ($fontOptions as $options) {
                if($options[0] != 'inherit') {
                    $tmpArray = array_unique(array_merge($tmpArray, $options));
                }
            }

            if ($fontKey == '_characterset') {
                $subset = implode(',', $tmpArray);
            } else {
                //$normalizedFontOptions[] = $fontKey . ":" . implode(',', $tmpArray);
                if($fontKey != 'inherit') {
                    $normalizedFontOptions[] = $fontKey;
                }
            }
        }

        $fontUrl = implode('|', $normalizedFontOptions);

        if ($subset) {
            $fontUrl .= '&subset=' . $subset;
        }

        return $fontUrl;
    }
}
