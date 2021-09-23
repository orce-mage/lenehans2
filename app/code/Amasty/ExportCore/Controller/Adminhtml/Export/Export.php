<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Controller\Adminhtml\Export;

use Amasty\ExportCore\Export\Config\ProfileConfigFactory;
use Amasty\ExportCore\Export\FormProvider;
use Amasty\ExportCore\Model\ConfigProvider;
use Amasty\ExportCore\Processing\JobManager;
use Amasty\ExportCore\Ui\DataProvider\Export\CompositeFormType;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Export extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ExportCore::export';

    /**
     * @var ProfileConfigFactory
     */
    private $profileConfigFactory;

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var FormProvider
     */
    private $formProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Action\Context $context,
        ProfileConfigFactory $profileConfigFactory,
        FormProvider $formProvider,
        ConfigProvider $configProvider,
        JobManager $jobManager
    ) {
        parent::__construct($context);
        $this->profileConfigFactory = $profileConfigFactory;
        $this->jobManager = $jobManager;
        $this->formProvider = $formProvider;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParam('encodedData');
        if (!empty($data)) {
            $params = $this->getRequest()->getParams();
            unset($params['encodedData']);
            $postData = \json_decode($data, true);
            $this->getRequest()->setParams(array_merge_recursive($params, $postData));
        }
        /** @var \Amasty\ExportCore\Export\Config\ProfileConfig $profileConfig */
        $profileConfig = $this->profileConfigFactory->create();
        $profileConfig->setStrategy('export');
        $profileConfig->setEntityCode($this->getRequest()->getParam('entity_code'));
        $profileConfig->setIsUseMultiProcess($this->configProvider->useMultiProcess());
        $profileConfig->setMaxJobs($this->configProvider->getMaxProcessCount());
        $this->formProvider->get(CompositeFormType::TYPE)->prepareConfig($profileConfig, $this->getRequest());
        $profileConfig->initialize();

        try {
            $result = ['type' => 'success'];
            $this->jobManager->requestJob($profileConfig, $this->getRequest()->getParam('processIdentity'));
        } catch (\Exception $e) {
            $result = ['type' => 'error', 'message' => $e->getMessage()];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
