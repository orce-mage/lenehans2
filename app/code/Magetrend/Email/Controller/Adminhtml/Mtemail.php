<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Controller\Adminhtml;

abstract class Mtemail extends \Magento\Backend\App\Action
{

    protected $_resultJsonFactory = null;

    protected $_coreRegistry = null;

    protected $_sessionManager = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_sessionManager = $session;
        parent::__construct($context);
    }

    /**
     * Check if user has enough privileges
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Email::template');
    }

    protected function _error($message)
    {
        return $this->_resultJsonFactory->create()->setData([
            'error' => $message
        ]);
    }

    protected function _jsonResponse($data)
    {
        return $this->_resultJsonFactory->create()->setData($data);
    }
}
