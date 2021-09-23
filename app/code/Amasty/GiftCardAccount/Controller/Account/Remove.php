<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Account;

use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\CustomerCard\Repository as CustomerCardRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Remove extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CustomerCardRepository
     */
    private $customerCardRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        CustomerCardRepository $customerCardRepository,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->customerCardRepository = $customerCardRepository;
        $this->session = $session;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        if (!$this->configProvider->isEnabled()) {
            throw new NotFoundException(__('Invalid Request'));
        }

        if (!$this->session->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $accountId = (int)$this->getRequest()->getParam('account_id');
            $currentCustomerId = (int)$this->session->getCustomerId();
            $model = $this->customerCardRepository->getByAccountAndCustomerId($accountId, $currentCustomerId);

            if ($model->getCustomerId() == $currentCustomerId) {
                $this->customerCardRepository->delete($model);
                $response = ['message' => __('Gift Card has been successfully removed'), 'error' => false];
            } else {
                $response = ['message' => __('Specified Gift Card for current user is not found.'), 'error' => true];
            }
        } catch (\Exception $e) {
            $response = ['message' => __('Cannot remove gift card.'), 'error' => true];
        }

        return $resultJson->setData($response);
    }
}
