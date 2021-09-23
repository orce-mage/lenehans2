<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Controller\Adminhtml\Export;

use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;
use Amasty\ExportCore\Model\Process\Process;
use Amasty\ExportCore\Processing\JobManager;
use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;

class Download extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ExportCore::export';

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var TmpFileManagement
     */
    private $tmp;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        Action\Context $context,
        TmpFileManagement $tmp,
        FileFactory $fileFactory,
        JobManager $jobManager
    ) {
        parent::__construct($context);
        $this->jobManager = $jobManager;
        $this->tmp = $tmp;
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        if ($processIdentity = $this->getRequest()->getParam('processIdentity')) {
            /** @var $exportResult ExportResultInterface */
            /** @var $process Process */
            [$process, $exportResult] = $this->jobManager->watchJob($processIdentity)->getJobState();
            if ($exportResult !== null
                && $process->getStatus() === Process::STATUS_SUCCESS
                && $exportResult->getResultFileName()
            ) {
                $tempDirectory = $this->tmp->getTempDirectory($process->getIdentity());
                if (!$tempDirectory->stat($this->tmp->getResultTempFileName($process->getIdentity()))['size']) {
                    $this->messageManager->addErrorMessage(__('Export File is empty'));

                    return $this->_redirect($this->_redirect->getRefererUrl());
                }

                $tmpFilename = $this->tmp->getResultTempFileName($process->getIdentity());
                $this->fileFactory->create(
                    $exportResult->getResultFileName(),
                    [
                        'type' => 'filename',
                        'value' => $tempDirectory->getAbsolutePath($tmpFilename)
                    ],
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'application/octet-stream',
                    $tempDirectory->stat($tmpFilename)['size']
                );

                return null;
            }
        }

        $this->messageManager->addErrorMessage(__('Something went wrong'));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
