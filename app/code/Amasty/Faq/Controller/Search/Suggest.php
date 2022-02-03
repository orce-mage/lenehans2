<?php

namespace Amasty\Faq\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Amasty\Faq\Model\Search\Autocomplete\DataProvider as Autocomplete;
use Magento\Framework\Controller\ResultFactory;

class Suggest extends Action
{
    /**
     * @var Autocomplete
     */
    private $autocomplete;

    public function __construct(
        Context $context,
        Autocomplete $autocomplete
    ) {
        $this->autocomplete = $autocomplete;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());

            return $resultRedirect;
        }

        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $resultItem) {
            $responseData[] = $resultItem->toArray();
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);

        return $resultJson;
    }
}
