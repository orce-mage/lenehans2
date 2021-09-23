<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ProcessOrderCreation implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardCartProcessor
     */
    private $gCardCartProcessor;

    public function __construct(
        Repository $accountRepository,
        GiftCardCartProcessor $gCardCartProcessor,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->accountRepository = $accountRepository;
        $this->gCardCartProcessor = $gCardCartProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $model = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote = $model->getQuote();

        if ($code = $request['amgiftcard_add'] ?? '') {
            try {
                $account = $this->accountRepository->getByCode(trim($code));
                $this->gCardCartProcessor->applyToCart($account, $quote);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Failed to apply this gift card.'));
            }
        }

        if ($code = $request['amgiftcard_remove'] ?? '') {
            try {
                $account = $this->accountRepository->getByCode($code);
                $this->gCardCartProcessor->removeFromCart($account, $quote);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Failed to remove this gift card.'));
            }
        }
    }
}
