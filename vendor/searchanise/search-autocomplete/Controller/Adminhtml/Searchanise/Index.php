<?php

namespace Searchanise\SearchAutocomplete\Controller\Adminhtml\Searchanise;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page as ResultPage;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ResultPage
     */
    private $resultPage;

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ApiSeHelper $apiSeHelper
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->apiSeHelper = $apiSeHelper;
    }

    public function execute()
    {
        $this->apiSeHelper->checkConnections();
        $this->apiSeHelper->checkQueue();
        $this->apiSeHelper->checkCurrentEngine();
        $this->apiSeHelper->checkIncompatibilities();

        $this->_setPageData();

        return $this->getResultPage();
    }

    /**
     * TODO: add 'ACL' permission for admin
     protected function _isAllowed()
     {
        return $this->_authorization->isAllowed('SY_Callback::requests');
     }
     */

    private function getResultPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultPageFactory->create();
        }

        return $this->resultPage;
    }

    private function _setPageData()
    {
        $resultPage = $this->getResultPage();

        $resultPage->setActiveMenu('Magento_Catalog::catalog');

        return $this;
    }
}
