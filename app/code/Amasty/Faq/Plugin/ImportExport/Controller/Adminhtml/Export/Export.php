<?php

namespace Amasty\Faq\Plugin\ImportExport\Controller\Adminhtml\Export;

use Amasty\Faq\Api\ImportExport\ExportInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\ImportExport\Model\Export as ExportModel;

class Export
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
     * @var ExportModel
     */
    private $exportModel;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        MessageManager $messageManager,
        ExportModel $exportModel,
        FileFactory $fileFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->exportModel = $exportModel;
        $this->sessionManager = $sessionManager;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Overridden, because ExportModel::FILTER_ELEMENT_GROUP is not exist
     *
     * @param \Magento\ImportExport\Controller\Adminhtml\Export\Export $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $data = $this->request->getParams();
        if (!in_array($data['entity'], ExportInterface::EXPORT_TYPES)) {
            return $proceed();
        }
        try {
            $this->exportModel->setData($this->request->getParams());

            $this->sessionManager->writeClose();
            $this->exportModel->setData(ExportModel::FILTER_ELEMENT_GROUP, []);
            if (!$this->exportModel->hasData(ExportModel::FILTER_ELEMENT_SKIP)) {
                $this->exportModel->setData(ExportModel::FILTER_ELEMENT_SKIP, []);
            }
            return $this->fileFactory->create(
                $this->exportModel->getFileName(),
                $this->exportModel->export(),
                DirectoryList::VAR_DIR,
                $this->exportModel->getContentType()
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');

        return $resultRedirect;
    }
}
