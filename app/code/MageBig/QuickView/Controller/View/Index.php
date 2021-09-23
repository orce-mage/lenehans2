<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\QuickView\Controller\View;

class Index extends \Magento\Catalog\Controller\Product
{
    protected $productHelper;
    protected $resultPage;
    protected $resultForward;

    /**
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \Magento\Catalog\Helper\Product\View                $productHelper
     * @param \Magento\Framework\View\Result\PageFactory          $resultPage
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForward
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Helper\Product\View $productHelper,
        \Magento\Framework\View\Result\PageFactory $resultPage,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForward
    ) {
        $this->productHelper = $productHelper;
        $this->resultForward = $resultForward;
        $this->resultPage    = $resultPage;
        parent::__construct($context);
    }

    public function execute()
    {
        $categoryId     = (int) $this->getRequest()->getParam('category', false);
        $productId      = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();
            if (!$product) {
                return $this->noProductRedirect();
            }
            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNotice($notice);
            }
            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode([
                        'backUrl' => $this->_redirect->getRedirectUrl(),
                    ])
                );

                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();

            return $resultRedirect;
        }

        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        try {
            $page = $this->resultPage->create(false, ['isIsolated' => true, 'template' => 'MageBig_QuickView::root.phtml']);
            $page->addDefaultHandle();
            $this->productHelper->prepareAndRender($page, $productId, $this, $params);

            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductView();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $resultForward = $this->resultForward->create();
            $resultForward->forward('noroute');

            return $resultForward;
        }
    }

    protected function noProductView($message = '')
    {
        $html = '<div class="message info error"><div>' . $message . '</div></div>';
        $this->getResponse()->setBody($html);
    }
}
