<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Controller\Adminhtml\Import;

use Amasty\ImportCore\Model\Process\ProcessRepository;
use Amasty\ImportCore\Processing\JobManager;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Import extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ImportCore::import';

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    public function __construct(
        Action\Context $context,
        ProcessRepository $processRepository,
        JobManager $jobManager
    ) {
        parent::__construct($context);
        $this->jobManager = $jobManager;
        $this->processRepository = $processRepository;
    }

    public function execute()
    {
        $result = [];
        if ($processIdentity = $this->getRequest()->getParam('processIdentity')) {
            try {
                $profileConfig = $this->processRepository->getByIdentity($processIdentity)->getProfileConfig();
                $profileConfig->setStrategy('import');
                $this->jobManager->requestJob($profileConfig, $processIdentity);
                $result = ['type' => 'success'];
            } catch (LocalizedException $e) {
                $result['error'] = __('Requested Process Identity not found.');
            } catch (\Exception $e) {
                $result = ['type' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $result['error'] = __('Process Identity is not set.');
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
