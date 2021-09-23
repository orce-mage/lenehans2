<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Email\Block\Adminhtml;

use Magento\Email\Block\Adminhtml\Template;

class TemplatePlugin
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
     * @param  \Magento\Framework\Registry $registry
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
     * Update email template grid
     * There is no another possibility to add
     * @param Template $template
     */
    public function beforeGetCreateUrl(Template $template)
    {
        if ($this->registry->registry('mtemail_button_added') != 1) {
            $template->addButton(
                'mteditor_import',
                [
                    'label' => __('Import'),
                    'class' => 'import'
                ]
            );

            $template->addButton(
                'mteditor',
                [
                    'label' => __('Create New with MTEditor'),
                    'onclick' => "window.location='" . $template->getUrl('mtemail/mteditor/index') . "'",
                    'class' => 'add primary add-template'
                ]
            );
            $this->registry->register('mtemail_button_added', 1);
        }
    }
}
