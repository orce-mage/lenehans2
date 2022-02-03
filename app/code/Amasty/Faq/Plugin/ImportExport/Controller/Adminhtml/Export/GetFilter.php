<?php

namespace Amasty\Faq\Plugin\ImportExport\Controller\Adminhtml\Export;

use Amasty\Faq\Api\ImportExport\ExportInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\ImportExport\Model\Export as ModelExport;

class GetFilter
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var Export
     */
    private $export;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        MessageManager $messageManager,
        ModelExport $export
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->export = $export;
    }

    /**
     * Subject can be overridden by \Magento\AdvancedPricingImportExport\Controller\Adminhtml\Export\GetFilter
     * Around plugin to change block for attribute list
     *
     * @param \Magento\ImportExport\Controller\Adminhtml\Export\GetFilter
     * |\Magento\AdvancedPricingImportExport\Controller\Adminhtml\Export\GetFilter $subject
     * @param \Closure $proceed
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout|mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $data = $this->request->getParams();
        if (!in_array($data['entity'], ExportInterface::EXPORT_TYPES)) {
            return $proceed();
        }
        if ($this->request->isXmlHttpRequest() && $data) {

            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            /** @var $resultLayout \Magento\Framework\View\Result\Layout */
            $resultLayout->getDefaultLayoutHandle();
            /** @var $attrFilterBlock \Amasty\Faq\Block\Adminhtml\Export\Filter */
            $attrFilterBlock = $resultLayout->getLayout()->addBlock(
                \Amasty\Faq\Block\Adminhtml\Export\Filter::class,
                ExportInterface::BLOCK_NAME,
                'root'
            );

            $isQuestion = $data['entity'] == ExportInterface::QUESTION_EXPORT ? true : false;
            $attrFilterBlock->setIsQuestion($isQuestion);
            $this->export->setData($data);

            return $resultLayout;
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');

        return $resultRedirect;
    }
}
