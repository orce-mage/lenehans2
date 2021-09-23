<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Sales;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler as CreditmemoReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\SaveHandler as CreditmemoSaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler as OrderSaveHandler;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class CreditmemoRepositoryPlugin
{
    /**
     * @var OrderReadHandler
     */
    private $orderReadHandler;

    /**
     * @var OrderSaveHandler
     */
    private $orderSaveHandler;

    /**
     * @var CreditmemoReadHandler
     */
    private $memoReadHandler;

    /**
     * @var CreditmemoSaveHandler
     */
    private $memoSaveHandler;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface|null
     */
    private $memoExtension = null;

    public function __construct(
        OrderReadHandler $orderReadHandler,
        OrderSaveHandler $orderSaveHandler,
        CreditmemoReadHandler $memoReadHandler,
        CreditmemoSaveHandler $memoSaveHandler,
        Repository $accountRepository,
        OrderRepositoryInterface $orderRepository,
        CollectionFactory $accountCollectionFactory
    ) {
        $this->orderReadHandler = $orderReadHandler;
        $this->orderSaveHandler = $orderSaveHandler;
        $this->memoReadHandler = $memoReadHandler;
        $this->memoSaveHandler = $memoSaveHandler;
        $this->accountRepository = $accountRepository;
        $this->orderRepository = $orderRepository;
        $this->accountCollectionFactory = $accountCollectionFactory;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     */
    public function afterGet(
        CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $creditmemo
    ): CreditmemoInterface {
        if ($order = $creditmemo->getOrder()) {
            $this->orderReadHandler->loadAttributes($order);
        }
        $this->memoReadHandler->loadAttributes($creditmemo);

        return $creditmemo;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param SearchResultsInterface $searchResult
     *
     * @return SearchResultsInterface
     */
    public function afterGetList(
        CreditmemoRepositoryInterface $subject,
        SearchResultsInterface $searchResult
    ): SearchResultsInterface {
        foreach ($searchResult->getItems() as $creditmemo) {
            $this->afterGet($subject, $creditmemo);
        }

        return $searchResult;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $creditmemo
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function beforeSave(
        CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $creditmemo
    ) {
        if ($order = $creditmemo->getOrder()) {
            $this->orderSaveHandler->saveAttributes($order);
        }

        if ($creditmemo->getExtensionAttributes() && $creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo()) {
            $this->memoExtension = $creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo();
        }
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(
        CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $creditmemo
    ): CreditmemoInterface {
        if ($gCardMemo = $this->memoExtension) {
            $extension = $creditmemo->getExtensionAttributes();
            $gCardMemo->setCreditmemoId((int)$creditmemo->getId());
            $extension->setAmGiftcardCreditmemo($gCardMemo);
            $creditmemo->setExtensionAttributes($extension);
            $this->memoSaveHandler->saveAttributes($creditmemo);
        }
        $this->deleteGiftCardAccounts($creditmemo);

        return $creditmemo;
    }

    /**
     * Delete created accounts if order contained gift card PRODUCTS
     *
     * @param CreditmemoInterface $creditmemo
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteGiftCardAccounts(CreditmemoInterface $creditmemo)
    {
        $order = ($creditmemo->getOrder()) ?: $this->orderRepository->get((int)$creditmemo->getOrderId());

        if ($orderItems = $order->getItems()) {
            foreach ($creditmemo->getItems() as $creditMemoItem) {
                $orderItem = $orderItems[$creditMemoItem->getOrderItemId()] ?? null;

                if ($orderItem && $codes = $this->getGiftCodes($orderItem)) {
                    $this->removeGiftCardAccounts(
                        $codes
                    );
                }
            }
        }
    }

    /**
     * @param array $codes
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function removeGiftCardAccounts(array $codes)
    {
        /** @var \Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection $collection */
        $collection = $this->accountCollectionFactory->create();
        $accounts = $collection->addCodeTable()
            ->addFieldToFilter('code', ['in' => $codes])
            ->getItems();

        foreach ($accounts as $account) {
            $this->accountRepository->delete($account);
        }
    }

    private function getGiftCodes(OrderItemInterface $orderItem): array
    {
        $codes = (array)$orderItem->getProductOptionByCode(GiftCardOptionInterface::GIFTCARD_CREATED_CODES);
        $qtyRefunded = $orderItem->getQtyRefunded();

        if (count($codes) > $qtyRefunded) {
            $codes = array_slice($codes, 0, $qtyRefunded);
        }

        return $codes;
    }
}
