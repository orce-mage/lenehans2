<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Backend\Block\Widget\Grid;

use \Magento\Backend\Block\Widget\Grid\ColumnSet;

class ColumnSetPlugin
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    public $mtHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;
    /**
     * GridPlugin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Email\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\Email\Helper\Data $helper
    ) {
        $this->mtHelper = $helper;
        $this->registry = $registry;
    }

    /**
     * Create and store grid row url
     * @param ColumnSet $columnSet
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function beforeGetRowUrl(ColumnSet $columnSet, \Magento\Framework\DataObject $item)
    {
        if ($columnSet->getGrid()->getId() == 'systemEmailTemplateGrid'
            && $this->mtHelper->isActive()
            && $item->getIsMtEmail()
        ) {
            $url = $columnSet->getUrl('mtemail/mteditor/index', ['id' => $item->getId()]);
            $this->registry->register('mtemail-grid-row-url', $url);
        }
    }

    /**
     * Update grid url if template is mt email
     * @param ColumnSet $columnSet
     * @param  string $url
     * @return string
     */
    public function afterGetRowUrl(ColumnSet $columnSet, $url)
    {
        if ($columnSet->getGrid()->getId() == 'systemEmailTemplateGrid'
            && $this->mtHelper->isActive()
            && $this->registry->registry('mtemail-grid-row-url') != ''
        ) {
            $url = $this->registry->registry('mtemail-grid-row-url');
            $this->registry->unregister('mtemail-grid-row-url');
        }
        return $url;
    }
}
