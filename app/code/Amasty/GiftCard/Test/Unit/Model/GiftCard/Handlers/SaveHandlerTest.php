<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\GiftCard\Handlers;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\GiftCardPrice;
use Amasty\GiftCard\Model\GiftCard\GiftCardPriceRepository;
use Amasty\GiftCard\Model\GiftCard\Handlers\SaveHandler;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCard\Model\GiftCard\Handlers\SaveHandler
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    const LINK_FILED = 'entity_id';
    const PRODUCT_ID = 10;
    const ATTRIBUTE_ID = 5;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var GiftCardPriceRepository|MockObject
     */
    private $priceRepository;

    /**
     * @var Repository|MockObject
     */
    private $attributeRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $metadata = $this->createPartialMock(EntityMetadata::class, ['getLinkField']);
        $metadata->expects($this->any())->method('getLinkField')->willReturn(self::LINK_FILED);
        $metadataPool = $this->createPartialMock(MetadataPool::class, ['getMetadata']);
        $metadataPool->expects($this->any())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);
        $this->priceRepository = $this->createPartialMock(
            GiftCardPriceRepository::class,
            ['getPricesByProductId', 'delete', 'save']
        );
        $this->attributeRepository = $this->createPartialMock(Repository::class, ['get']);
        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'metadataPool' => $metadataPool,
                'giftCardPriceRepository' => $this->priceRepository,
                'attributeRepository' => $this->attributeRepository
            ]
        );
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($amounts, $oldAmounts, $deleteCallCount, $saveCallCount)
    {
        /** @var Product|MockObject $product */
        $product = $this->createPartialMock(Product::class, ['getExtensionAttributes', 'getOrigData']);
        $product->setTypeId(GiftCard::TYPE_AMGIFTCARD);
        $product->setData(self::LINK_FILED, self::PRODUCT_ID);
        $product->expects($this->any())->method('getOrigData')->with(Attributes::GIFTCARD_PRICES)
            ->willReturn($oldAmounts);
        $extensionAttributes = $this->createPartialMock(ProductExtension::class, ['getAmGiftcardPrices']);
        $extensionAttributes->expects($this->once())->method('getAmGiftcardPrices')->willReturn($amounts);
        $product->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->priceRepository->expects($this->any())->method('getPricesByProductId')
            ->with(self::PRODUCT_ID)
            ->willReturn($oldAmounts);
        $this->priceRepository->expects($this->exactly($deleteCallCount))->method('delete');

        $attribute = $this->createPartialMock(Attribute::class, []);
        $attribute->setAttributeId(self::ATTRIBUTE_ID);
        $this->attributeRepository->expects($deleteCallCount ? $this->once() : $this->never())
            ->method('get')
            ->with(Attributes::GIFTCARD_PRICES)
            ->willReturn($attribute);
        $this->priceRepository->expects($this->exactly($saveCallCount))->method('save');

        $this->saveHandler->execute($product);
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\Handlers\SaveHandler::execute
     */
    public function testExecuteNotGiftCard()
    {
        $product = $this->createPartialMock(Product::class, []);
        $product->setTypeId('not_gift_card');

        $this->assertEquals($product, $this->saveHandler->execute($product));
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        $oldAmount = $this->createPartialMock(GiftCardPrice::class, []);

        $amountWithDataA = $this->createPartialMock(GiftCardPrice::class, []);
        $amountWithDataA->setData('value', 10);
        $amountWithDataB = $this->createPartialMock(GiftCardPrice::class, []);
        $amountWithDataB->setData('value', 20);

        $amountWithoutDataA = $this->createPartialMock(GiftCardPrice::class, []);
        $amountWithoutDataA->addData([]);
        $amountWithoutDataB = $this->createPartialMock(GiftCardPrice::class, []);
        $amountWithoutDataB->addData([]);

        return [
            [[], [], 0, 0],//no amounts entity, no old amounts
            [[], [$oldAmount], 1, 0],//no amounts entity, one old amount,
            [[$amountWithDataA], [$oldAmount], 1, 1],//one new amount with data
            [[$amountWithoutDataA], [$oldAmount], 1, 0],//one new amount without data
            [[$amountWithDataA, $amountWithDataB], [$oldAmount], 1, 2],//two amounts with data,
            [[$amountWithoutDataA, $amountWithoutDataB], [$oldAmount], 1, 0],//two amounts without data,
            [[$amountWithDataA, $amountWithoutDataA], [$oldAmount], 1, 1]//two amounts, one with data, second without
        ];
    }
}
