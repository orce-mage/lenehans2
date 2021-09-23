<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Code\ResourceModel\Code;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository as AccountRepository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory as AccountCollectionFactory;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Repository as GiftCardQuoteRepository;
use \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository as GiftCardOrderRepository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateOldData
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var AccountCollectionFactory
     */
    private $accountCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GiftCardQuoteRepository
     */
    private $giftCardQuoteRepository;

    /**
     * @var GiftCardOrderRepository
     */
    private $giftCardOrderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    public function __construct(
        AccountRepository $accountRepository,
        OrderCollectionFactory $orderCollectionFactory,
        AccountCollectionFactory $accountCollectionFactory,
        GiftCardQuoteRepository $giftCardQuoteRepository,
        GiftCardOrderRepository $giftCardOrderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        LoggerInterface $logger
    ) {
        $this->accountRepository = $accountRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->accountCollectionFactory = $accountCollectionFactory;
        $this->logger = $logger;
        $this->giftCardQuoteRepository = $giftCardQuoteRepository;
        $this->giftCardOrderRepository = $giftCardOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $this->updateAccounts($setup);
        $this->updateExtensionTables($setup);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function updateAccounts(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        $accountTable = $setup->getTable(Account::TABLE_NAME);
        $accountsWithOrderItemIds = $this->getOrderItemIdsForAccounts($setup);

        $setup->getConnection()->changeColumn(
            $accountTable,
            'order_id',
            GiftCardAccountInterface::ORDER_ITEM_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Order Item ID',
                'unsigned' => true,
                'nullable' => true
            ]
        );
        $accountCollection = $this->accountCollectionFactory->create()
            ->addFieldToFilter(GiftCardAccountInterface::ACCOUNT_ID, ['in' => array_keys($accountsWithOrderItemIds)]);

        /** @var GiftCardAccountInterface $account */
        foreach ($accountCollection->getItems() as $account) {
            $account->setOrderItemId((int)$accountsWithOrderItemIds[$account->getAccountId()] ?? null);
            try {
                $this->accountRepository->save($account);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function updateExtensionTables(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        $oldQuoteTable = $setup->getTable('amasty_amgiftcard_quote');

        if (!$setup->tableExists($oldQuoteTable)) {
            return;
        }
        $select = $setup->getConnection()->select()->from(
            $oldQuoteTable
        );
        //gift card quote table update
        $newQuoteData = $this->getNewQuoteData($setup->getConnection()->fetchAll($select));

        foreach ($newQuoteData as $quoteId => $quoteData) {
            $gCardQuote = $this->giftCardQuoteRepository->getEmptyQuoteModel();
            $gCardQuote->setQuoteId((int)$quoteId)->addData($quoteData);
            try {
                $this->giftCardQuoteRepository->save($gCardQuote);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        //gift card order table update
        $ordersWithGCard = $setup->getConnection()->fetchAssoc($this->orderCollectionFactory->create()
            ->addFieldToSelect(
                ['entity_id', 'quote_id']
            )->addFieldToFilter('quote_id', ['in' => array_keys($newQuoteData)])->getSelect());

        foreach ($ordersWithGCard as $orderId => $order) {
            $gCardOrder = $this->giftCardOrderRepository->getEmptyOrderModel();
            $gCardOrder->setOrderId((int)$orderId)->addData($newQuoteData[$order['quote_id']]);
            try {
                $this->giftCardOrderRepository->save($gCardOrder);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        //gift card invoice table update
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('order_id', array_keys($ordersWithGCard), 'in')->create();
        $invoicesWithGCard = $this->invoiceRepository->getList($searchCriteria);

        foreach ($invoicesWithGCard->getItems() as $invoice) {
            $order = $invoice->getOrder();
            $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
            $gCardInvoice = $invoice->getExtensionAttributes()->getAmGiftcardInvoice();
            $used = $invoice->getSubtotalInclTax() + $invoice->getShippingInclTax()
                - $invoice->getDiscountAmount() - $invoice->getGrandTotal();
            $baseUsed = $invoice->getBaseSubtotalInclTax() + $invoice->getBaseShippingInclTax()
                - $invoice->getBaseDiscountAmount() - $invoice->getBaseGrandTotal();
            $gCardOrder->setInvoiceGiftAmount($gCardOrder->getInvoiceGiftAmount() + $used);
            $gCardOrder->setBaseInvoiceGiftAmount($gCardOrder->getBaseInvoiceGiftAmount() + $baseUsed);
            $gCardInvoice->setGiftAmount($used);
            $gCardInvoice->setBaseGiftAmount($baseUsed);
            try {
                $this->invoiceRepository->save($invoice);
                $this->giftCardOrderRepository->save($gCardOrder);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        //credit memo table update
        $memoWithGCard = $this->creditmemoRepository->getList($searchCriteria);

        foreach ($memoWithGCard->getItems() as $memo) {
            $order = $memo->getOrder();
            $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
            $gCardMemo = $memo->getExtensionAttributes()->getAmGiftcardCreditmemo();
            $used = $memo->getSubtotalInclTax() + $memo->getShippingInclTax()
                - $memo->getDiscountAmount() - $memo->getGrandTotal();
            $baseUsed = $memo->getBaseSubtotalInclTax() + $memo->getBaseShippingInclTax()
                - $memo->getBaseDiscountAmount() - $memo->getBaseGrandTotal();
            $gCardOrder->setRefundGiftAmount($gCardOrder->getRefundGiftAmount() + $used);
            $gCardOrder->setBaseRefundGiftAmount($gCardOrder->getBaseRefundGiftAmount() + $used);
            $gCardMemo->setGiftAmount($used);
            $gCardMemo->setBaseGiftAmount($baseUsed);
            try {
                $this->creditmemoRepository->save($memo);
                $this->giftCardOrderRepository->save($gCardOrder);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        $setup->getConnection()->dropTable($oldQuoteTable);

        $setup->endSetup();
    }

    /**
     * Returns array where key is account id and value is order item id
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return array
     */
    private function getOrderItemIdsForAccounts(ModuleDataSetupInterface $setup)
    {
        $select = $setup->getConnection()->select()->from(
            ['ac' => $setup->getTable(Account::TABLE_NAME)],
            ['code.code', GiftCardAccountInterface::ACCOUNT_ID, 'order_id']
        )->joinLeft(
            ['code' => $setup->getTable(Code::TABLE_NAME)],
            'ac.' . GiftCardAccountInterface::CODE_ID . ' = code.' . CodeInterface::CODE_ID,
            []
        )->where(
            'ac.order_id'
        );
        $accountsWithOrder = $setup->getConnection()->fetchAssoc($select);
        $orderIds = [];

        foreach ($accountsWithOrder as $account) {
            $orderIds[] = $account['order_id'];
        }
        $accountsWithOrderItems = [];
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => array_unique($orderIds)])
            ->getItems();

        foreach ($orders as $order) {
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getProductType() !== GiftCard::TYPE_AMGIFTCARD) {
                    continue;
                }
                $productOptions = $orderItem->getProductOptions();
                $codes = $productOptions[GiftCardOptionInterface::GIFTCARD_CREATED_CODES] ?? [];

                foreach ($codes as $code) {
                    if (isset($accountsWithOrder[$code])) {
                        $accountsWithOrderItems[$accountsWithOrder[$code][GiftCardAccountInterface::ACCOUNT_ID]] =
                            $orderItem->getItemId();
                    }
                }
            }
        }

        return $accountsWithOrderItems;
    }

    /**
     * Prepare old gift card quote table data to migration
     *
     * @param array $oldQuoteData
     *
     * @return array
     */
    private function getNewQuoteData($oldQuoteData)
    {
        $newQuoteData = [];
        $quoteGiftCards = [];

        foreach ($oldQuoteData as $quoteData) {
            isset($newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT])
                ? $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT]
                += $quoteData['gift_amount']
                : $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT]
                = $quoteData['gift_amount'];
            isset($newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT_USED])
                ? $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT_USED]
                += $quoteData['gift_amount']
                : $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::GIFT_AMOUNT_USED]
                = $quoteData['gift_amount'];
            isset($newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT])
                ? $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT]
                += $quoteData['base_gift_amount']
                : $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT]
                = $quoteData['base_gift_amount'];
            isset($newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED])
                ? $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED]
                += $quoteData['base_gift_amount']
                : $newQuoteData[$quoteData['quote_id']][GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED]
                = $quoteData['base_gift_amount'];

            $quoteGiftCards[$quoteData['quote_id']][] = [
                GiftCardCartProcessor::GIFT_CARD_ID => $quoteData['account_id'],
                GiftCardCartProcessor::GIFT_CARD_CODE => $quoteData['code'],
                GiftCardCartProcessor::GIFT_CARD_AMOUNT => $quoteData['gift_amount'],
                GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => $quoteData['base_gift_amount']
            ];
        }

        foreach ($quoteGiftCards as $quoteId => $giftCard) {
            $newQuoteData[$quoteId][GiftCardQuoteInterface::GIFT_CARDS] = $giftCard;
        }

        return $newQuoteData;
    }
}
