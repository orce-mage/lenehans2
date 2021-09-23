<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Utils;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\Collection;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Utils\AccountGenerator;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;

class AccountGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AccountGenerator
     */
    private $accountGenerator;

    /**
     * @var ItemRepository|MockObject
     */
    private $repositoryMock;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repositoryMock = $this->createPartialMock(ItemRepository::class, ['save']);
        $this->accountGenerator = $this->objectManager->create(
            AccountGenerator::class,
            ['orderItemRepository' => $this->repositoryMock]
        );
    }

    /**
     * @dataProvider generateFromOrderItemDataProvider
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/order_with_giftcard_order_item.php
     *
     * @param array $productOptions
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function testGenerateFromOrderItem(array $productOptions)
    {
        $codePoolId = Bootstrap::getObjectManager()->create(Collection::class)->getLastItem()->getCodePoolId();
        $productOptions[Attributes::CODE_SET] = $codePoolId;

        /** @var Item $orderItem */
        $orderItem = $this->objectManager->create(Item::class)->load('amgiftcard', 'product_type');
        $orderItem->setProductOptions($productOptions);
        $orderItem->save();

        $this->repositoryMock->expects($this->atLeastOnce())->method('save')->with($orderItem);
        $this->accountGenerator->generateFromOrderItem($orderItem, 1);
    }

    public function generateFromOrderItemDataProvider(): array
    {
        return [
            'no additional options' => [[]],
            'additional options' => [[GiftCardOptionInterface::GIFTCARD_AMOUNT => 50]]
        ];
    }
}
