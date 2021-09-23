<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Plugin\Sales;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Collection;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Amasty\GiftCardAccount\Plugin\Sales\CreditmemoRepositoryPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item as MemoItem;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;

class CreditmemoRepositoryPluginTest extends \PHPUnit\Framework\TestCase
{
    const ORDER_ITEM_ID = 1;
    const ACCOUNT_CODE = 'test_code';

    /**
     * @var CreditmemoRepositoryPlugin
     */
    private $plugin;

    /**
     * @var Collection|MockObject
     */
    private $accountCollection;

    /**
     * @var Repository|MockObject
     */
    private $accountRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $accountCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->accountCollection = $this->createPartialMock(
            Collection::class,
            ['addCodeTable', 'addFieldToFilter', 'getItems']
        );
        $this->accountCollection->expects($this->any())->method('addCodeTable')
            ->willReturn($this->accountCollection);
        $this->accountCollection->expects($this->any())->method('addFieldToFilter')
            ->willReturn($this->accountCollection);
        $accountCollectionFactory->expects($this->any())->method('create')
            ->willReturn($this->accountCollection);
        $this->accountRepository = $this->createPartialMock(
            Repository::class,
            ['delete']
        );

        $this->plugin = $objectManager->getObject(
            CreditmemoRepositoryPlugin::class,
            [
                'accountCollectionFactory' => $accountCollectionFactory,
                'accountRepository' => $this->accountRepository
            ]
        );
    }

    /**
     * @covers \Amasty\GiftCardAccount\Plugin\Sales\CreditmemoRepositoryPlugin::deleteGiftCardAccounts
     * @covers \Amasty\GiftCardAccount\Plugin\Sales\CreditmemoRepositoryPlugin::removeGiftCardAccounts
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($accountCodes, $accounts, $deleteCallCount)
    {
        $subject = $this->createMock(CreditmemoRepositoryInterface::class);

        $memo = $this->createPartialMock(Creditmemo::class, ['getOrder', 'getItems']);
        $memoItem = $this->createPartialMock(MemoItem::class, []);
        $memoItem->setOrderItemId(self::ORDER_ITEM_ID);
        $memo->expects($this->any())->method('getItems')->willReturn([$memoItem]);

        $order = $this->createPartialMock(Order::class, ['getItems']);
        $orderItem = $this->createPartialMock(Item::class, ['getProductOptionByCode']);
        $orderItem->setId(self::ORDER_ITEM_ID);
        $orderItem->expects($this->any())->method('getProductOptionByCode')
            ->willReturn($accountCodes);
        $order->expects($this->any())->method('getItems')
            ->willReturn([self::ORDER_ITEM_ID => $orderItem]);
        $memo->expects($this->any())->method('getOrder')->willReturn($order);

        $this->accountCollection->expects($this->any())->method('getItems')
            ->willReturn($accounts);
        $this->accountRepository->expects($this->exactly($deleteCallCount))->method('delete');

        $this->plugin->afterSave($subject, $memo);
    }

    public function afterSaveDataProvider()
    {
        $accountsA = [$this->createPartialMock(Account::class, [])];
        $accountsB = [
            $this->createPartialMock(Account::class, []),
            $this->createPartialMock(Account::class, [])
        ];
        return [
            [['test_code_1'], $accountsA, 1],
            [['test_code_1', 'test_code_2'], $accountsB, 2],
            [null, [], 0]
        ];
    }
}
