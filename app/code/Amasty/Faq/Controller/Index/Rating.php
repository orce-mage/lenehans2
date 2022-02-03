<?php

declare(strict_types=1);

namespace Amasty\Faq\Controller\Index;

use Amasty\Faq\Model\Frontend\Rating\VotingService;
use Magento\Framework\App\Action\Context;

class Rating extends \Magento\Framework\App\Action\Action
{
    /**
     * @var VotingService
     */
    private $votingService;

    public function __construct(
        Context $context,
        VotingService $votingService
    ) {
        parent::__construct($context);
        $this->votingService = $votingService;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        if ($questionIds = $this->_request->getParam('items')) {
            $resultJson->setData($this->votingService->getVotingData(array_map('intval', $questionIds)));
        }

        return $resultJson;
    }
}
