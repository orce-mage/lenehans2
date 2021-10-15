<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class Transportbuilder
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class Transportbuilder
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Email\Model\BackendTemplate
     */
    protected $_backendTemplate;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Transportbuilder constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\BackendTemplate $backendTemplate
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Email\Model\BackendTemplate $backendTemplate
    )
    {
        $this->_backendTemplate = $backendTemplate;
        $this->backendSession = $backendSession;
    }

    /**
     * @param \Magento\Framework\Mail\Template\TransportBuilder $subject
     * @param $templateIdentifier
     */
    public function beforeSetTemplateIdentifier(\Magento\Framework\Mail\Template\TransportBuilder $subject, $templateIdentifier)
    {
        $this->_initTemplate($templateIdentifier);
    }

    /**
     * Load email template
     *
     * @param string $idFieldName
     * @return \Magento\Email\Model\BackendTemplate $model
     */
    protected function _initTemplate($templateIdentifier)
    {
        $model = null;
        if ($templateIdentifier) {
            $model = $this->_backendTemplate->load($templateIdentifier);
        }
        $this->backendSession->unsEmailTemplate();
        $this->backendSession->unsCurrentEmailTemplate();
        if(!$this->backendSession->getEmailTemplate()) {
            $this->backendSession->setEmailTemplate($templateIdentifier);
        }

        if(!$this->backendSession->getCurrentEmailTemplate()) {
            $this->backendSession->setCurrentEmailTemplate($templateIdentifier);
        }

        return $model;
    }
}
