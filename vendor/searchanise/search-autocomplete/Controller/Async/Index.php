<?php

namespace Searchanise\SearchAutocomplete\Controller\Async;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as DataSeHelper;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\resultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    const UPDATE_TIMEOUT = 3600;

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SeDataHelper
     */
    private $searchaniseHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool
     */
    private $notUseHttpRequestText = false;

    /**
     * @var bool
     */
    private $flShowStatusAsync = false;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ApiSeHelper $apiSeHelper,
        DataSeHelper $searchaniseHelper
    ) {
        $this->storeManager = $storeManager;
        $this->apiSeHelper = $apiSeHelper;
        $this->searchaniseHelper = $searchaniseHelper;

        parent::__construct($context);
    }

    /**
     * Get 'not_use_http_request' param for URL
     *
     * @return string
     */
    private function _getNotUseHttpRequestText()
    {
        $this->notUseHttpRequestText = $this
            ->getRequest()
            ->getParam(ApiSeHelper::NOT_USE_HTTP_REQUEST);

        return $this->notUseHttpRequestText;
    }

    /**
     * Check if 'not_use_http_request' param is true
     *
     * @return boolean
     */
    private function _checkNotUseHttpRequest()
    {
        return $this->_getNotUseHttpRequestText() == ApiSeHelper::NOT_USE_HTTP_REQUEST_KEY;
    }

    /**
     * Get 'show_status' param for URL
     *
     * @return string
     */
    private function _getFlShowStatusAsync()
    {
        $this->flShowStatusAsync = $this
            ->getRequest()
            ->getParam(ApiSeHelper::FL_SHOW_STATUS_ASYNC);

        return $this->flShowStatusAsync;
    }

    /**
     * Check if 'show_status' param is true
     *
     * @return boolean
     */
    private function _checkShowStatusAsync()
    {
        return $this->_getFlShowStatusAsync() == ApiSeHelper::FL_SHOW_STATUS_ASYNC_KEY;
    }

    /**
     * Async
     *
     * {@inheritDoc}
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        // ToCheck:
        $storeId = null;
        $result = 'OK';
        $flIgnoreProcessing = false;

        if ($this->apiSeHelper->getStatusModule($storeId) == ApiSeHelper::MODULE_STATUS_ENABLED) {
            $checkKey = $this->searchaniseHelper->checkPrivateKey();
            $this->apiSeHelper->setHttpResponse($this->getResponse());

            ignore_user_abort(true);
            set_time_limit(self::UPDATE_TIMEOUT);

            if ($checkKey && $this->getRequest()->getParam('display_errors') === 'Y') {
                error_reporting(E_ALL | E_STRICT);
            } else {
                error_reporting(0);
            }

            $flIgnoreProcessing = $checkKey && $this->getRequest()->getParam('ignore_processing') == 'Y';

            try {
                $result = $this->apiSeHelper->async($flIgnoreProcessing);
            } catch (\Exception $e) {
                $result = __('Error [%1] %2', $e->getCode(), $e->getMessage());
            }
        } else {
            $result = __('Module is disabled');
        }

        return $this->resultFactory->create(resultFactory::TYPE_JSON)
            ->setData([
                'status' => $result,
            ]);
    }
}
