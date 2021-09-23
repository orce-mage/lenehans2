<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Email\Controller\Adminhtml\Template;

use \Magento\Email\Controller\Adminhtml\Email\Template\Preview;

class PreviewPlugin
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $redirectFactory;

    /**
     * PreviewPlugin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magetrend\Email\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magetrend\Email\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->mtHelper = $helper;
        $this->registry = $registry;
        $this->objectManager = $objectManagerInterface;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Redirect mt email template preview
     * @param Preview $preview
     * @param \callable $processed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(Preview $preview, $processed)
    {
        if (!$this->mtHelper->isActive(0)) {
            return $processed();
        }
        $templateId = $preview->getRequest()->getParam('id');
        if (!is_numeric($templateId)) {
            return $processed();
        }
        $model = $this->objectManager->create('Magento\Email\Model\BackendTemplate');
        $model->load($templateId);
        if ($model->getIsMtEmail() == 1) {
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath($preview->getUrl('mtemail/mteditor/preview', ['id' => $templateId]));
            return $resultRedirect;
        }
        return $processed();
    }
}
