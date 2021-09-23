<?php

namespace Searchanise\SearchAutocomplete\Controller\Adminhtml\Searchanise;

use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

class Resync extends Action
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    public function __construct(Context $context, ApiSeHelper $apiSeHelper)
    {
        $this->apiSeHelper = $apiSeHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->apiSeHelper->getStatusModule() == ApiSeHelper::MODULE_STATUS_ENABLED) {
            if (!$this->apiSeHelper->signup(null, false)) {
                $this->_redirect($this->apiSeHelper->getSearchaniseLink());
            }

            $this->apiSeHelper->queueImport();
            $this->_redirect($this->apiSeHelper->getSearchaniseLink());
        }
    }
}
