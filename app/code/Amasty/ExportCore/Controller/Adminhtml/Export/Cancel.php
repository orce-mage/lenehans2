<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Controller\Adminhtml\Export;

use Amasty\ExportCore\Export\Utils\CleanUpByProcessIdentity;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Cancel extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_ExportCore::export';

    /**
     * @var CleanUpByProcessIdentity
     */
    private $cleanUpByProcessIdentity;

    public function __construct(
        Action\Context $context,
        CleanUpByProcessIdentity $cleanUpByProcessIdentity
    ) {
        parent::__construct($context);
        $this->cleanUpByProcessIdentity = $cleanUpByProcessIdentity;
    }

    public function execute()
    {
        $result = [];
        if ($processIdentity = $this->getRequest()->getParam('processIdentity')) {
            $this->cleanUpByProcessIdentity->execute($processIdentity);
            $result['type'] = 'success';
        } else {
            $result['error'] = __('Process Identity is not set.');
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
