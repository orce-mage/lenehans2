<?php

/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Controller\Adminhtml\Template;

use Magento\Framework\App\TemplateTypesInterface;

/**
 * Class Send
 * @package WeltPixel\EnhancedEmail\Controller\Adminhtml\Template
 */
class Send extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var Data|\WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Send constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_wpHelper = $wpHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->backendSession = $backendSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute()
    {
        $post = $this->getRequest()->getPost();
        $request = $this->getRequest();
        $result = $this->_resultJsonFactory->create();
        $id = $this->getRequest()->getParam('is_edit');
        $template = $this->_initTemplate('is_edit');
        if (!$template->getId() && $id) {
            $this->messageManager->addError(__('This email template no longer exists.'));
        }

        $templateCode = $request->getParam('code');
        $originalTemplateCode = $request->getParam('send_orig_template_code');

        try {
            $template->setTemplateSubject(
                $request->getParam('subject')
            )->setTemplateCode(
                $templateCode
            )->setTemplateText(
                $request->getParam('text')
            )->setTemplatePreheader(
                $request->getParam('template_preheader')
            )->setTemplateStyles(
                $request->getParam('styles')
            )->setModifiedAt(
                $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class)->gmtDate()
            )->setOrigTemplateCode(
                $originalTemplateCode
            )->setOrigTemplateVariables(
                $request->getParam('send_orig_template_variables')
            )->setIsLegacy(1);

            if (!$template->getId()) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
            }

            if ($request->getParam('_change_type_flag')) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            $template->save();
        } catch (\Exception $e) {
            $result->setData(['response' => $e->getMessage()]);
            return $result;
        }

        $this->inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($template->getId());
        $this->_transportBuilder->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ]
        );
        $emailSampleData = $this->_wpHelper->getEmailSampleData($originalTemplateCode);
        $this->_transportBuilder->setTemplateVars($emailSampleData);
        $senderInfo = $this->_wpHelper->getEmailSender();
        $this->_transportBuilder->setFrom($senderInfo);
        $this->_transportBuilder->addTo(
            $post['email'],
            'Test Name'
        );
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
        $result->setData(['response' => '']);

        return $result;
    }

    /**
     * Load email template from reques
     * @param string $idFieldName
     * @return \Magento\Email\Model\BackendTemplate $model
     */
    protected function _initTemplate($idFieldName = 'template_id')
    {
        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = $this->_objectManager->create(\Magento\Email\Model\BackendTemplate::class);
        if ($id) {
            $model->load($id);
        }

        $this->backendSession->unsEmailTemplate($id);
        $this->backendSession->unsCurrentEmailTemplate($id);
        if(!$this->backendSession->getEmailTemplate()) {
            $this->backendSession->setEmailTemplate($id);
        }

        if(!$this->backendSession->getCurrentEmailTemplate()) {
            $this->backendSession->setCurrentEmailTemplate($id);
        }
        return $model;
    }

}
