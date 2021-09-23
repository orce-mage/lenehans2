<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Processing\JobManager;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Status extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ImportCore::import';

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
            /** @var $importResult ImportResultInterface */
            list($process, $importResult) = $this->jobManager->watchJob($processIdentity)->getJobState();
            if ($importResult === null) {
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
                $resultMessages =
                    array_merge($importResult->getMessages(), $importResult->getPreparedValidationMessages());
                $result = [
                    'status' =>  $process->getStatus(),
                    'proceed' => $importResult->getRecordsProcessed(),
                    'total' => $importResult->getTotalRecords(),
                    'messages' => $resultMessages
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
