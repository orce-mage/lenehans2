<?php

namespace Swissup\SoldTogether\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Swissup\SoldTogether\Model\ResourceModel\AbstractResourceModel;

abstract class AbstractCondense extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AbstractResourceModel
     */
    protected $resourceModel;

    /**
     * @param Context               $context
     * @param PageFactory           $resultPageFactory
     * @param AbstractResourceModel $resourceModel
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        AbstractResourceModel $resourceModel

    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Condense action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($this->resourceModel->isCondenseDataRequired()) {
            $this->resourceModel->condenseData();
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $path = '';
        if ($backPath = $this->getRequest()->getParam('back')) {
            $path = $this->decamelizePath($backPath);
        }

        $params = [];
        if ($id = $this->getRequest()->getParam('id')) {
            $params['id'] = $id;
        }

        return $resultRedirect->setPath($path, $params);
    }

    public function decamelizePath($camelizedPath)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '/$0', $camelizedPath));
    }
}
