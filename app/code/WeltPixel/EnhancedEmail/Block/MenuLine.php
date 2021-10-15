<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\View\Element\Template;
use WeltPixel\EnhancedEmail\Helper\Data;

/**
 * Class MenuLine
 * @package WeltPixel\EnhancedEmail\Block
 */
class MenuLine extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * @var
     */
    protected $_storeId;

    /**
     * MenuLine constructor.
     * @param Data $_helper
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Data $_helper,
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        array $data = []
    )
    {
        $this->_storeId = ($this->_storeManager) ? $this->_storeManager->getStore()->getId() : null;
        $this->_helper = $_helper;
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->getMenu(), 'block' => $this, 'request' => $this->getRequest()]
        );

        $this->getMenu()->setOutermostClass($outermostClass);
        $this->getMenu()->setChildrenWrapClass($childrenWrapClass);

        foreach ($this->getMenu()->getChildren() as $node) {

            if ($node->getChildren()) {
                foreach ($node->getChildren() as $subMenu) {
                    $node->removeChild($subMenu);
                }
            }

        }


        $html = $this->_getHtml($this->getMenu(), $childrenWrapClass, $limit);

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->getMenu(), 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    )
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        /** @var \Magento\Framework\Data\Tree\Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            if (count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><h3 style=\'display:inline-block !important; margin: 0 !important\'>' . $this->escapeHtml(
                    $child->getName()
                ) . '</h3></a>' . $this->_addSubMenu(
                    $child,
                    $childLevel,
                    $childrenWrapClass,
                    $limit
                ) . '</li>';
            $itemPosition++;
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * @return mixed
     */
    public function getTopmenuBgColor()
    {
        return $this->_helper->getTopmenuBgColor($this->_storeId);
    }

    /**
     * @return mixed
     */
    public function getTopmenuPaddingTopBottom()
    {
        return $this->_helper->getTopmenuPaddingTopBottom($this->_storeId);
    }

    /**
     * @return mixed
     */
    public function getTopmenuPadding()
    {
        return $this->_helper->getTopmenuPadding($this->_storeId);
    }

    /**
     * @return mixed
     */
    public function getTopmenuFontColor()
    {
        return $this->_helper->getTopmenuFontColor($this->_storeId);
    }

}