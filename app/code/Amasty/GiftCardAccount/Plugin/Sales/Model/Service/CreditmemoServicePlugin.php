<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Sales\Model\Service;

use Amasty\GiftCardAccount\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver;
use Amasty\GiftCardAccount\Model\RefundStrategy;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Service\CreditmemoService;
use Psr\Log\LoggerInterface;

class CreditmemoServicePlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RefundStrategy
     */
    private $refundStrategy;

    /**
     * @var GiftCardExtensionResolver
     */
    private $gCardExtensionResolver;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        RefundStrategy $refundStrategy,
        GiftCardExtensionResolver $gCardExtensionResolver,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->refundStrategy = $refundStrategy;
        $this->gCardExtensionResolver = $gCardExtensionResolver;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function afterRefund(
        CreditmemoService $subject,
        Creditmemo $result
    ) {
        $order = $result->getOrder();
        $storeId = $order->getStore()->getId();

        try {
            if ($this->configProvider->isEnabled($storeId)
                && $this->configProvider->isRefundAllowed($storeId)
            ) {
                $this->refundGiftCardAccounts($result);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }

    private function refundGiftCardAccounts(Creditmemo $creditmemo): void
    {
        $gCardMemo = $this->gCardExtensionResolver->resolve($creditmemo);
        if (!$gCardMemo || $gCardMemo->getBaseGiftAmount() < .0) {
            return;
        }
        $order = $creditmemo->getOrder();
        $totalAmount = $gCardMemo->getBaseGiftAmount();
        $refundedAccounts = $this->refundStrategy->refundToAccount($order, $totalAmount);
        $refundedAmount = array_sum(array_column($refundedAccounts, RefundStrategy::KEY_AMOUNT));
        $refundedCodes = implode(',', array_column($refundedAccounts, RefundStrategy::KEY_CODE));

        $order->addCommentToStatusHistory(__(
            '%1 (in store\'s base currency) has been refunded to Gift Card Account(s) %2.',
            [
                $order->getBaseCurrency()->formatTxt($refundedAmount),
                $refundedCodes
            ]
        ))->setIsCustomerNotified(false);
        $this->orderRepository->save($order);
    }
}
