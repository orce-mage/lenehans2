<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Controller\Adminhtml\Export;

use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Processing\JobManager;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Status extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ExportCore::export';

    /**
     * @var JobManager
     */
    private $jobManager;

    public function __construct(
        Action\Context $context,
        JobManager $jobManager
    ) {
        parent::__construct($context);
        $this->jobManager = $jobManager;
    }

    public function execute()
    {
        $result = [];
        if ($processIdentity = $this->getRequest()->getParam('processIdentity')) {
            /** @var $exportResult ExportResultInterface */
            list($process, $exportResult) = $this->jobManager->watchJob($processIdentity)->getJobState();
            if ($exportResult === null) {
                $result = [
                    'status' =>  'starting',
                    'proceed' => 0,
                    'total' => 0,
                    'messages' => [
                        [
                            'type' => 'info',
                            'message' => __('Process Started')
                        ]
                    ]
                ];
            } else {
                $result = [
                    'status' =>  $process->getStatus(),
                    'proceed' => $exportResult->getRecordsProcessed(),
                    'total' => $exportResult->getTotalRecords(),
                    'messages' => $exportResult->getMessages()
                ];
            }
        } else {
            $result['error'] = __('Process Identity is not set.');
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
