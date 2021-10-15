<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Adminhtml\Template;

/**
 * Class Edit
 * @package WeltPixel\EnhancedEmail\Block\Adminhtml\Template
 */
class Edit extends \Magento\Email\Block\Adminhtml\Template\Edit
{

    /**
     * Template file
     *
     * @var string
     */
    protected $_template = 'template/edit.phtml';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'send_test_email',
            [
                'label' => __('Save & Send Test Email'),
                'data_attribute' => [
                    'role' => 'template-send',
                ],
                'class' => 'save primary'

            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Load template url
     *
     * @return string
     */
    public function getSendTestEmailUrl()
    {
        return $this->getUrl('weltpixel_enhancedemail/template/send');
    }

    /**
     * @return mixed
     */
    public function getStoreEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isEditPage()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return false;
        }

        return $id;
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }
}