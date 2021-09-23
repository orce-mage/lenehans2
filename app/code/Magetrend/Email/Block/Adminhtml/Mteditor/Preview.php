<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Adminhtml\Mteditor;

class Preview extends \Magento\Backend\Block\Template
{
    public $coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function getEmailTemplateHtml()
    {
        $data = $this->coreRegistry->registry('mteditor_preview_content');
        return $data['content'];
    }

    public function getEmailTemplateStyle()
    {
        $data = $this->coreRegistry->registry('mteditor_preview_content');
        return $data['css'];
    }
}
