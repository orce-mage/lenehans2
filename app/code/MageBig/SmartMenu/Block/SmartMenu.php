<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Block;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\Group;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Catalog\Helper\Category as HelperCategory;

class SmartMenu extends Template
{

    /**
     * @var FilterProvider
     */
    protected $_cmsFilter;

    /**
     * @var HelperCategory
     */
    protected $_helperCategory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var HttpContext
     */
    protected $httpContext;


    /**
     * @var Layer
     */
    protected $_catalogLayer;

    /**
     * Current category key
     *
     * @var string
     */
    protected $_currentCategoryKey;

    /**
     * @var array
     */
    protected $_catPosLevel = [];

    /**
     * @var State
     */
    protected $flatState;

    /**
     * @var bolean
     */
    protected $_isTouch;

    protected $_isFlat;

    protected $_baseUrl;

    protected $_mediaUrl;

    const MAGEBIG_SMARTMENU = 'MAGEBIG_SMARTMENU';

    /**
     * SmartMenu constructor.
     * @param Template\Context $context
     * @param HelperCategory $helperCategory
     * @param FilterProvider $cmsFilter
     * @param Registry $registry
     * @param State $flatState
     * @param HttpContext $httpContext
     * @param Resolver $layerResolver
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Template\Context $context,
        HelperCategory $helperCategory,
        FilterProvider $cmsFilter,
        Registry $registry,
        State $flatState,
        HttpContext $httpContext,
        Resolver $layerResolver,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_helperCategory = $helperCategory;
        $this->_cmsFilter = $cmsFilter;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->httpContext = $httpContext;
        $this->_catalogLayer = $layerResolver->get();
        $this->_storeManager = $storeManager;
        $this->_isFlat = $this->flatState->isFlatEnabled();
        $this->_baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $this->_mediaUrl = $this->getMediaUrl();
        parent::__construct($context, $data);

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [Category::CACHE_TAG, Group::CACHE_TAG]
        ]);
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getCacheKeyInfo()
    {
        $shortCacheId = [
            self::MAGEBIG_SMARTMENU,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentCategoryKey(),
        ];
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrentCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrentCategoryKey()
    {
        if (!$this->_currentCategoryKey) {
            $category = $this->_registry->registry('current_category');
            if ($category) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = $this->_storeManager->getStore()->getRootCategoryId();
            }
        }

        return $this->_currentCategoryKey;
    }

    /**
     * Checkin activity of category
     *
     * @param DataObject $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->_catalogLayer->getCurrentCategory();
    }

    /**
     * @param $block
     *
     * @return string
     * @throws Exception
     */
    protected function _getStatic($block)
    {
        return $this->_cmsFilter->getBlockFilter()->filter(trim($block));
    }

    public function getHtml($catLevel = 0, $outerClass = '', $childWrapClass = '', $vertical = false)
    {
        if ($childWrapClass == 'nav-mobile' || $childWrapClass == 'cat-pagemenu') {
            $this->_isTouch = true;
        } else {
            $this->_isTouch = false;
        }
        $html = '';
        $catHelper = $this->_helperCategory;
        foreach ($catHelper->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                if ($this->_isTouch) {
                    $html .= $this->_catSimpleHtml($child, $catLevel, $outerClass, $childWrapClass, $vertical);
                } else {
                    $catStyle = $child->getData('smartmenu_cat_style');

                    if ($catStyle == 'dropdown_mega' || $catStyle == 'dropdown_group') {
                        $html .= $this->_catMegaHtml($child, $catLevel, $outerClass, $childWrapClass);
                    } elseif ($catStyle == 'dropdown_simple') {
                        $html .= $this->_catSimpleHtml($child, $catLevel, $outerClass, $childWrapClass);
                    } else {
                        $html .= $this->_catSimpleHtml($child, $catLevel, $outerClass,
                            'simple-dropdown ' . $childWrapClass);
                    }
                }
            }
        }

        return $html;
    }

    protected function _catMegaHtml($catInfo, $catLevel = 0, $outerClass = '', $childWrapClass = '')
    {
        if (!$catInfo->getIsActive() || $catInfo->getData('include_in_menu') == 0) {
            return '';
        }
        $html = '';

        if ($this->_isFlat && $catInfo->getUseFlatResource()) {
            $children = (array)$catInfo->getChildrenNodes();
        } else {
            $children = $catInfo->getChildren();
        }

        $activeChildren = [];
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);
        $catStyle = $catInfo->getData('smartmenu_cat_style');
        if ($catInfo->getData('smartmenu_cat_position') == 'fullwidth') {
            $subwidth = '100%';
        } else {
            $subwidth = $catInfo->getData('smartmenu_cat_dropdown_width') ? $catInfo->getData('smartmenu_cat_dropdown_width') : '270px';
        }

        $showblock = $hasActiveChildren;

        if ($catLevel == 1) {
            if ($catInfo->getData('smartmenu_static_top') || $catInfo->getData('smartmenu_static_left') || $catInfo->getData('smartmenu_static_right') || $catInfo->getData('smartmenu_static_bottom')) {
                $showblock = true;
            }
        }

        // Custom URL
        $noclick = '';
        if ($custom_url = $catInfo->getData('smartmenu_cat_target')) {
            if ($custom_url === '#') {
                $cat_link = '#';
                $classes[] = 'no-click';
                $noclick = ' onclick="return false;"';
            } elseif ($custom_url = trim($custom_url)) {
                if (strpos($custom_url, 'http') === 0) {
                    $cat_link = $custom_url;
                } else {
                    $cat_link = $this->_baseUrl . $custom_url;
                }
            } else {
                $cat_link = $this->_getUrl($catInfo);
            }
        } else {
            $cat_link = $this->_getUrl($catInfo);
        }

        $classes = ['category-item'];
        $classes[] = 'level' . $catLevel;
        if ($catLevel == 1) {
            $classes[] = 'groups item';
        }
        $classes[] = 'nav-' . $this->_getCatPosition($catLevel);
        if ($this->isCategoryActive($catInfo)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($outerClass) {
            $classes[] = $outerClass;
            $linkClass = ' class="' . $outerClass . '"';
        }

        if ($hasActiveChildren && $catLevel >= 1) {
            $classes[] = 'mega_' . $catInfo->getData('smartmenu_cat_position');
            $classes[] = 'parent';
        }
        if ($hasActiveChildren && $catLevel == 0 && $showblock) {
            $classes[] = 'mega_' . $catInfo->getData('smartmenu_cat_position');
            $classes[] = 'parent';
        }

        $attributes = [];
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }

        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html .= $htmlLi;
        if ($catLevel == 1 && $showblock) {
            if ($catInfo->getData('smartmenu_static_top')) {
                $html .= '<div class="mbmenu-block mbmenu-block-level1-top std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_top'));
                $html .= '</div>';
            }
        }
        $html .= '<a href="' . $cat_link . '"' . $linkClass . $noclick . '>';

        if ($iconHtml = $catInfo->getData('smartmenu_cat_icon')) {
            $iconHtml = $this->escapeHtml($iconHtml, ['i', 'span']);
            $html .= $iconHtml;
        } elseif ($iconImage = $catInfo->getData('smartmenu_cat_imgicon')) {
            $iconImage = str_replace('catalog/tmp/', 'catalog/', $iconImage);
            if (strpos($iconImage, '/catalog/')) {
                $iconHtml = '<img width="20" height="20" alt="' . $catInfo->getData('name') . '" src="' . $this->_baseUrl . $iconImage . '">';
            } else {
                $iconHtml = '<img width="20" height="20" alt="' . $catInfo->getData('name') . '" src="' . $this->_mediaUrl . 'catalog/category/' . $iconImage . '">';
            }

            $html .= $iconHtml;
        }

        $labelCategory = $this->_getCatLabel($catInfo, $catLevel);
        if ($catLevel == 1) {
            $html .= '<span class="title_group">' . __($this->escapeHtml($catInfo->getName())) . $labelCategory . '</span>';
        } else {
            $html .= '<span>' . __($this->escapeHtml($catInfo->getName())) . $labelCategory . '</span>';
        }
        $html .= '</a>';
        if ($hasActiveChildren && $catLevel == 0) {
            $html .= '<i class="mbi mbi-ios-arrow-down"></i>';
        }

        if ($catLevel == 0) {
            $catStaticRight = $this->_getStatic($catInfo->getData('smartmenu_static_right'));
            $catStaticLeft = $this->_getStatic($catInfo->getData('smartmenu_static_left'));
            if ($catInfo->getData('smartmenu_block_right') || $catInfo->getData('smartmenu_block_left')) {
                $columns = $catInfo->getData('smartmenu_cat_column');
                $widthRight = $catInfo->getData('smartmenu_block_right');
                $widthLeft = $catInfo->getData('smartmenu_block_left');
            } else {
                if ($catInfo->getData('smartmenu_cat_column') == '') {
                    $columns = 3;
                } else {
                    $columns = $catInfo->getData('smartmenu_cat_column');
                }
                $widthRight = 1;
                $widthLeft = 1;
            }
            $goups = $widthRight + $widthLeft;
            if (empty($catStaticRight) || empty($catStaticLeft) || $catStyle == 'dropdown_group') {
                if (empty($catStaticRight)) {
                    $gridCount1 = 'col12-' . (12 - $widthLeft);
                    $gridCountLeft = 'col12-' . ($widthLeft);
                }
                if (empty($catStaticLeft)) {
                    $gridCount1 = 'col12-' . (12 - $widthRight);
                    $gridCountRight = 'col12-' . ($widthRight);
                }
                if (empty($catStaticRight) && empty($catStaticLeft) || $catStyle == 'dropdown_group') {
                    $gridCount1 = 'col12-12';
                }
            } elseif (!$hasActiveChildren) {
                $gridCountRight = 'col12-' . $widthRight;
                $gridCountLeft = 'col12-' . $widthLeft;
            } else {
                $grid = 12 - $goups;
                $gridCount1 = 'col12-' . ($grid);
                $gridCountRight = 'col12-' . ($widthRight);
                $gridCountLeft = 'col12-' . ($widthLeft);
            }
        }

        $li = '';
        foreach ($activeChildren as $child) {
            $li .= $this->_catMegaHtml($child, ($catLevel + 1), $outerClass, $childWrapClass);
        }

        if ($childWrapClass && $showblock) {
            if ($catStyle == 'dropdown_group') {
                if ($catLevel == 1) {
                    $html .= '<div class="groups-wrapper">';
                    $html .= '<div class="show-sub-content">';
                } else {
                    $html .= '<div class="level' . $catLevel . ' simple-dropdown ' . $childWrapClass . ' show-sub" style="width: ' . $subwidth . ';height:auto;">';
                    $html .= '<div class="show-sub-content">';
                }
            } else {
                if ($catLevel == 1) {
                    $html .= '<div class="groups-wrapper">';
                    $html .= '<div class="show-sub-content">';
                } else {
                    $html .= '<div class="level' . $catLevel . ' ' . $childWrapClass . ' show-sub" style="width: ' . $subwidth . '; height:auto;">';
                    $html .= '<div class="show-sub-content">';
                }
            }
        }
        if ($catLevel == 0 && $showblock) {
            if ($catInfo->getData('smartmenu_static_top')) {
                $html .= '<div class="mbmenu-block mbmenu-block-top grid-full std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_top'));
                $html .= '</div>';
            }
            if ($catStyle != 'dropdown_group') {
                if ($catInfo->getData('smartmenu_static_left') && $widthLeft) {
                    $html .= '<div class="menu-static-blocks mbmenu-block mbmenu-block-left ' . $gridCountLeft . '">';
                    $html .= $this->_getStatic($catInfo->getData('smartmenu_static_left'));
                    $html .= '</div>';
                }
            }
        }
        if (!empty($li) && $hasActiveChildren) {
            if ($catLevel == 0) {
                $colCenter = 'itemgrid itemgrid-' . $columns . 'col';
                $html .= '<div class="mbmenu-block mbmenu-block-center menu-items ' . $gridCount1 . ' ' . $colCenter . '">';
            }
            $html .= '<ul class="level' . $catLevel . '">';
            $html .= $li;
            $html .= '</ul>';
            if ($catLevel == 0) {
                $html .= '</div>';
            }
        }

        if ($catLevel == 0 && $showblock) {
            if ($catStyle != 'dropdown_group') {
                if ($catInfo->getData('smartmenu_static_right') && $widthRight) {
                    $html .= '<div class="menu-static-blocks mbmenu-block mbmenu-block-right ' . $gridCountRight . '">';
                    $html .= $this->_getStatic($catInfo->getData('smartmenu_static_right'));
                    $html .= '</div>';
                }
            }
            if ($catInfo->getData('smartmenu_static_bottom')) {
                $html .= '<div class="mbmenu-block mbmenu-block-bottom grid-full std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_bottom'));
                $html .= '</div>';
            }
        }
        if ($childWrapClass && $showblock) {
            $html .= '</div>';
            $html .= '</div>';
        }

        if ($catLevel == 1 && $showblock) {
            if ($catInfo->getData('smartmenu_static_bottom')) {
                $html .= '<div class="mbmenu-block mbmenu-block-level1-top std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_bottom'));
                $html .= '</div>';
            }
        }
        $html .= '</li>';

        return $html;
    }

    protected function _catSimpleHtml(
        $catInfo,
        $catLevel = 0,
        $outerClass = '',
        $childWrapClass = '',
        $vertical = false
    )
    {
        if (!$catInfo->getIsActive()) {
            return '';
        }
        if ($catInfo->getData('include_in_menu') == 0) {
            return '';
        }
        if ($vertical == true && $catInfo->getData('smartmenu_show_on_cat') == 0) {
            return '';
        }
        $html = '';

        if ($this->_isFlat && $catInfo->getUseFlatResource()) {
            $children = (array)$catInfo->getChildrenNodes();
        } else {
            $children = $catInfo->getChildren();
        }

        $activeChildren = [];
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);
        $catStyle = $catInfo->getData('smartmenu_cat_style');
        if ($catInfo->getData('smartmenu_cat_position') == 'fullwidth') {
            $subwidth = '100%';
        } else {
            $subwidth = $catInfo->getData('smartmenu_cat_dropdown_width') ? $catInfo->getData('smartmenu_cat_dropdown_width') : '270px';
        }

        $showblock = $hasActiveChildren;

        // Custom URL
        $noclick = '';
        if ($custom_url = $catInfo->getData('smartmenu_cat_target')) {
            if ($custom_url === '#') {
                $cat_link = '#';
                $classes[] = 'no-click';
                $noclick = ' onclick="return false;"';
            } elseif ($custom_url = trim($custom_url)) {
                if (strpos($custom_url, 'http') === 0) {
                    $cat_link = $custom_url;
                } else {
                    $cat_link = $this->_baseUrl . $custom_url;
                }
            } else {
                $cat_link = $this->_getUrl($catInfo);
            }
        } else {
            $cat_link = $this->_getUrl($catInfo);
        }

        $classes = [];
        $classes[] = 'level' . $catLevel;
        if ($catLevel == 1) {
            $classes[] = 'item';
        }
        $classes[] = 'nav-' . $this->_getCatPosition($catLevel);

        if ($this->isCategoryActive($catInfo)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($outerClass) {
            $classes[] = $outerClass;
            $linkClass = ' class="' . $outerClass . '"';
        }

        if ($hasActiveChildren && $catLevel >= 1) {
            $classes[] = 'mega_' . $catInfo->getData('smartmenu_cat_position');
            $classes[] = 'parent';
        }
        if ($hasActiveChildren && $catLevel == 0 && $showblock && $this->_isTouch == false) {
            $classes[] = 'mega_' . $catInfo->getData('smartmenu_cat_position');
            $classes[] = 'parent';
        }

        $attributes = [];
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }

        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';
        $html .= $htmlLi;
        if ($catLevel == 1 && $showblock && $this->_isTouch == false) {
            if ($catInfo->getData('smartmenu_static_top')) {
                $html .= '<div class="mbmenu-block mbmenu-block-level1-top std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_top'));
                $html .= '</div>';
            }
        }
        $labelCategory = $this->_getCatLabel($catInfo, $catLevel);
        $html .= '<a href="' . $cat_link . '"' . $linkClass . $noclick . '>';

        if ($iconHtml = $catInfo->getData('smartmenu_cat_icon')) {
            $iconHtml = $this->escapeHtml($iconHtml, ['i', 'span']);
            $html .= $iconHtml;
        } elseif ($iconImage = $catInfo->getData('smartmenu_cat_imgicon')) {
            $iconImage = str_replace('catalog/tmp/', 'catalog/', $iconImage);
            if (strpos($iconImage, '/catalog/')) {
                $iconHtml = '<img width="20" height="20" alt="' . $catInfo->getData('name') . '" src="' . $this->_baseUrl . $iconImage . '">';
            } else {
                $iconHtml = '<img width="20" height="20" alt="' . $catInfo->getData('name') . '" src="' . $this->_mediaUrl . 'catalog/category/' . $iconImage . '">';
            }

            $html .= $iconHtml;
        }

        $html .= '<span>' . __($this->escapeHtml($catInfo->getName())) . $labelCategory . '</span>';
        $html .= '</a>';
        if ($hasActiveChildren && $catLevel == 0 && $childWrapClass != 'nav-mobile') {
            $html .= '<i class="mbi mbi-ios-arrow-down"></i>';
        }

        if ($catLevel == 0 && $this->_isTouch == false) {
            $catStaticRight = $this->_getStatic($catInfo->getData('smartmenu_static_right'));
            $catStaticLeft = $this->_getStatic($catInfo->getData('smartmenu_static_left'));
            if ($catInfo->getData('smartmenu_block_right') || $catInfo->getData('smartmenu_block_left')) {
                $columns = $catInfo->getData('smartmenu_cat_column');
                $widthRight = $catInfo->getData('smartmenu_block_right');
                $widthLeft = $catInfo->getData('smartmenu_block_left');
            } else {
                if ($catInfo->getData('smartmenu_cat_column') == '') {
                    $columns = 4;
                } else {
                    $columns = $catInfo->getData('smartmenu_cat_column');
                }
                $widthRight = 1;
                $widthLeft = 1;
            }
            $goups = $widthRight + $widthLeft;
            if (empty($catStaticRight) || empty($catStaticLeft) || $catStyle == 'dropdown_simple') {
                if (empty($catStaticRight)) {
                    $gridCount1 = 'col12-' . (12 - $widthLeft);
                    $gridCountLeft = 'col12-' . ($widthLeft);
                }
                if (empty($catStaticLeft)) {
                    $gridCount1 = 'col12-' . (12 - $widthRight);
                    $gridCountRight = 'col12-' . ($widthRight);
                }
                if (empty($catStaticRight) && empty($catStaticLeft) || $catStyle == 'dropdown_simple') {
                    $gridCount1 = 'col12-12';
                }
            } elseif (!$hasActiveChildren) {
                $gridCountRight = 'col12-' . $widthRight;
                $gridCountLeft = 'col12-' . $widthLeft;
            } else {
                $grid = 12 - $goups;
                $gridCount1 = 'col12-' . ($grid);
                $gridCountRight = 'col12-' . ($widthRight);
                $gridCountLeft = 'col12-' . ($widthLeft);
            }
        }

        $li = '';
        foreach ($activeChildren as $child) {
            $li .= $this->_catSimpleHtml($child, ($catLevel + 1), $outerClass, $childWrapClass);
        }

        if ($childWrapClass && $showblock && $this->_isTouch == false) {
            if ($catStyle == 'dropdown_simple') {
                $html .= '<div class="simple-dropdown ' . $childWrapClass . ' show-sub" style="width: ' . $subwidth . '; height:auto;">';
                $html .= '<div class="show-sub-content">';
            } else {
                $html .= '<div class="' . $childWrapClass . ' show-sub" style="width: ' . $subwidth . '; height:auto;">';
                $html .= '<div class="show-sub-content">';
            }
        }

        if ($catLevel == 0 && $showblock && $this->_isTouch == false) {
            if ($catInfo->getData('smartmenu_static_top')) {
                $html .= '<div class="mbmenu-block mbmenu-block-top grid-full std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_top'));
                $html .= '</div>';
            }
            if ($catStyle != 'dropdown_simple') {
                if ($catInfo->getData('smartmenu_static_left') && $widthLeft) {
                    $html .= '<div class="menu-static-blocks mbmenu-block mbmenu-block-left ' . $gridCountLeft . '">';
                    $html .= $this->_getStatic($catInfo->getData('smartmenu_static_left'));
                    $html .= '</div>';
                }
            }
        }
        if (!empty($li) && $hasActiveChildren) {
            if ($catLevel == 0 && $this->_isTouch == false) {
                $colCenter = 'itemgrid itemgrid-' . $columns . 'col';
                $html .= '<div class="mbmenu-block mbmenu-block-center menu-items ' . $gridCount1 . ' ' . $colCenter . '">';
            }
            $html .= '<ul class="level' . $catLevel . '">';
            $html .= $li;
            $html .= '</ul>';
            if ($catLevel == 0 && $this->_isTouch == false) {
                $html .= '</div>';
            }
        }
        if ($catLevel == 0 && $showblock && $this->_isTouch == false) {
            if ($catStyle != 'dropdown_simple') {
                if ($catInfo->getData('smartmenu_static_right') && $widthRight) {
                    $html .= '<div class="menu-static-blocks mbmenu-block mbmenu-block-right ' . $gridCountRight . '">';
                    $html .= $this->_getStatic($catInfo->getData('smartmenu_static_right'));
                    $html .= '</div>';
                }
            }
            if ($catInfo->getData('smartmenu_static_bottom')) {
                $html .= '<div class="mbmenu-block mbmenu-block-bottom grid-full std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_bottom'));
                $html .= '</div>';
            }
        }

        if ($childWrapClass && $showblock && $this->_isTouch == false) {
            $html .= '</div>';
            $html .= '</div>';
        }

        if ($catLevel == 1 && $showblock && $this->_isTouch == false) {
            if ($catInfo->getData('smartmenu_static_bottom') && $catStyle != 'dropdown_simple') {
                $html .= '<div class="mbmenu-block mbmenu-block-level1-top std">';
                $html .= $this->_getStatic($catInfo->getData('smartmenu_static_bottom'));
                $html .= '</div>';
            }
        }
        $html .= '</li>';

        return $html;
    }

    protected function _getCatLabel($catInfo, $catLevel)
    {
        $catLabel = $catInfo->getData('smartmenu_cat_label');
        if ($catLabel) {
            if ($catLevel == 0) {
                return ' <span class="cat-label cat-label-' . $catLabel . ' pin-bottom">' . __($catLabel) . '</span>';
            } else {
                return ' <span class="cat-label cat-label-' . $catLabel . '">' . __($catLabel) . '</span>';
            }
        }

        return '';
    }

    /**
     * @param $catLevel
     *
     * @return string
     */
    protected function _getCatPosition($catLevel)
    {
        if ($catLevel == 0) {
            $startLevel = isset($this->_catPosLevel[$catLevel]) ? $this->_catPosLevel[$catLevel] + 1 : 1;
            $this->_catPosLevel = [];
            $this->_catPosLevel[$catLevel] = $startLevel;
        } elseif (isset($this->_catPosLevel[$catLevel])) {
            ++$this->_catPosLevel[$catLevel];
        } else {
            $this->_catPosLevel[$catLevel] = 1;
        }

        $position = [];
        for ($i = 0; $i <= $catLevel; ++$i) {
            if (isset($this->_catPosLevel[$i])) {
                $position[] = $this->_catPosLevel[$i];
            }
        }

        return implode('-', $position);
    }

    /**
     * @param $catInfo
     * @return string
     */
    protected function _getUrl($catInfo)
    {
        if (!$this->_isFlat && !$catInfo->getUseFlatResource()) {
            if ($catInfo->getRequestPath()) {
                $catUrl = $this->_baseUrl . $catInfo->getRequestPath();
            } else {
                $catUrl = $this->_helperCategory->getCategoryUrl($catInfo);
            }
        } else {
            $catUrl = $catInfo->getUrl();
        }

        return $catUrl;
    }
}
