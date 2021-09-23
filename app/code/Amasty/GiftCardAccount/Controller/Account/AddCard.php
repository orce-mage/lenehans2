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
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository as AccountRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;

class AddCard extends \Magento\Framework\App\Action\Action
{
    const AM_GIFTCARD_CODE_KEY = 'am_giftcard_code';
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var CustomerCardRepository
     */
    private $customerCardRepository;
    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardAccountFormatter
     */
    private $accountFormatter;

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
        AccountRepository $accountRepository,
        CustomerCardRepository $customerCardRepository,
        \Amasty\GiftCardAccount\Model\GiftCardAccountFormatter $accountFormatter,
        ConfigProvider $configProvider
    ) {
        parent::__construct($context);
        $this->accountRepository = $accountRepository;
        $this->customerCardRepository = $customerCardRepository;
        $this->session = $session;
        $this->accountFormatter = $accountFormatter;
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

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = [];

        $code = trim($this->getRequest()->getParam(self::AM_GIFTCARD_CODE_KEY, ''));
        try {
            $account = $this->accountRepository->getByCode($code);
        } catch (LocalizedException $e) {
            $response = [
                'message' => __('Wrong Gift Card code.'),
                'error'   => true
            ];

            return $resultPage->setData($response);
        }
        $accountId = $account->getAccountId();

        if ($this->customerCardRepository->hasCardForAccountId($accountId)) {
            $response = [
                'message' => __('This Gift Code already exists.'),
                'error'   => true
            ];

            return $resultPage->setData($response);
        }
        $customerCard = $this->customerCardRepository->getEmptyCustomerCardModel();
        $customerCard->setCustomerId((int)$this->session->getCustomerId())
            ->setAccountId($accountId);
        $this->customerCardRepository->save($customerCard);
        $cards = $this->accountRepository->getAccountsByCustomerId((int)$this->session->getCustomerId());

        foreach ($cards as $card) {
            $response[] = $this->accountFormatter->getFormattedData($card);
        }

        return $resultPage->setData($response);
    }
}
