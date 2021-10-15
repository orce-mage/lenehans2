<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class EmailTemplateFormPlugin
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class EmailTemplateFormPlugin
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Email\Model\BackendTemplate
     */
    protected $_templateModel;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * EmailTemplateFormPlugin constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Email\Model\BackendTemplate $templateModel
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Email\Model\BackendTemplate $templateModel,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
    ) {
        //$this->_coreRegistry = $registry;
        $this->_request = $request;
        $this->_templateModel = $templateModel;
        $this->_wpHelper = $wpHelper;
    }

    /**
     * @param \Magento\Email\Block\Adminhtml\Template\Edit\Form $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        \Magento\Email\Block\Adminhtml\Template\Edit\Form $subject,
        \Closure $proceed
    ) {
        $form = $subject->getForm();
        $emailTemplate = $form->getParent()->getEmailTemplate();
        if (is_object($form)) {
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'template_preheader',
                'textarea',
                [
                    'name' => 'template_preheader',
                    'label' => __('Email First Line'),
                    'id' => 'template_preheader',
                    'required' => false,
                    'onkeyup' => 'templateControl.updateTemplateContent(this);',
                    'note' => 'Email preheader content.',
                    'value' => $emailTemplate->getTemplatePreheader()
                ],
                'template_subject'
            );


            $subject->setForm($form);
        }

        return $proceed();
    }


}
