<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @var \Magetrend\Email\Model\Varmanager|null
     */
    private $varManager = null;

    /**
     * @var \Magetrend\Email\Model\Varmanager
     */
    public $varManagerModel;

    /**
     * Items constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magetrend\Email\Model\Varmanager $varmanager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Email\Model\Varmanager $varmanager,
        array $data = []
    ) {
        $this->varManagerModel = $varmanager;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        //replace theme
        $template = $this->getTemplate();
        $theme = $this->getParentBlock()->getTheme();
        if ($theme != 'default') {
            $template = str_replace('/default/', '/'.$theme.'/', $template);
            $this->setTemplate($template);
        }

        return parent::_beforeToHtml();
    }

    public function getVarModel()
    {
        if ($this->varManager == null) {
            $this->varManager = $this->varManagerModel;
            $this->varManager->setTemplateId($this->getTemplateId());
            $this->varManager->setBlockId($this->getBlockId());
            $this->varManager->setBlockName($this->getBlockName());
        }
        return $this->varManager;
    }

    public function isRTL()
    {
        return $this->getDirection() == 'rtl';
    }

    public function getDirection()
    {
        return 'ltr';
    }

    public function getItems()
    {
        if ($this->hasData('creditmemo')) {
            return $this->getCreditmemo()->getAllItems();
        } elseif ($this->hasData('shipment')) {
            return $this->getShipment()->getAllItems();
        } elseif ($this->hasData('invoice')) {
            return $this->getInvoice()->getAllItems();
        } elseif ($this->hasData('quote')) {
            return $this->getQuote()->getAllItems();
        }

        return $this->getOrder()->getAllItems();
    }

    public function hasParent($item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            $childItem = $item;
        } elseif ($item instanceof \Magento\Sales\Model\Order\Item) {
            $childItem = $item;
        } else {
            $childItem = $item->getOrderItem();
        }

        if (!$childItem->getParentItem()) {
            return false;
        }

        return true;
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  string
     */
    public function getItemHtml(\Magento\Framework\DataObject $item)
    {

        $type = $this->_getItemType($item);

        $block = $this->getItemRenderer($type)
            ->setItem($item)
            ->setTheme($this->getTheme());
        $this->_prepareItem($block);
        return $block->toHtml();
    }
}
