<?php

namespace Searchanise\SearchAutocomplete\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use \Searchanise\SearchAutocomplete\Helper\Notification as SeNotification;

class Async extends Template
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SeNotification
     */
    private $notificationHelper;

    public function __construct(
        Context $context,
        ApiSeHelper $apiSeHelper,
        SeNotification $notificationHelper,
        array $data = []
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->notificationHelper = $notificationHelper;

        parent::__construct($context, $data);
    }

    private function _startSignup()
    {
        if ($this->apiSeHelper->signup(null, false) == true) {
            $this->apiSeHelper->queueImport(null, false);
        }

        return true;
    }

    protected function _prepareLayout()
    {
        if ($this->apiSeHelper->getIsAdmin()) {
            $textNotification = '';

            if ($this->apiSeHelper->checkAutoInstall()) {
                $textNotification = __(
                    'Searchanise was successfully installed. Catalog indexation in process. <a href="%1">Searchanise Admin Panel</a>.',
                    $this->apiSeHelper->getModuleUrl()
                );
            } elseif ($this->apiSeHelper->checkModuleIsUpdated()) {
                $this->apiSeHelper->updateInsalledModuleVersion();
                $textNotification = __(
                    'Searchanise was successfully updated. Catalog indexation in process. <a href="%1">Searchanise Admin Panel</a>.',
                    $this->apiSeHelper->getModuleUrl()
                );
            }

            if ($textNotification != '') {
                $this->notificationHelper->setNotification(
                    \Searchanise\SearchAutocomplete\Helper\Notification::TYPE_NOTICE,
                    __('Notice'),
                    $textNotification
                );

                // ToDo: to check
                $this->_startSignup();
            } else {
                // ToDo: to check
                $this->apiSeHelper->showNotificationAsyncCompleted();
            }
        }

        return parent::_prepareLayout();
    }

    public function getAsyncUrl($storeId = null)
    {
        $asyncUrl = $this->apiSeHelper->getAsyncUrl(false, $storeId);

        if ($this->apiSeHelper->checkObjectAsync()) {
            $asyncUrl .= (strpos($asyncUrl, '?') == false ? '?' : '&') . 't=' . time();
        }

        return $asyncUrl;
    }

    public function getIsObjectAsync()
    {
        return $this->apiSeHelper->checkObjectAsync();
    }

    public function getIsAjaxAsync()
    {
        return $this->apiSeHelper->checkAjaxAsync();
    }
}
