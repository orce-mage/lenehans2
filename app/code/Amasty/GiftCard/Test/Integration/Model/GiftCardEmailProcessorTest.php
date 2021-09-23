<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Model;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\GiftCardEmailProcessor;
use Amasty\GiftCard\Model\Image\Image;
use Amasty\GiftCard\Model\Image\ImageBakingProcessor;
use Amasty\GiftCard\Utils\EmailSender;
use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Item;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;

class GiftCardEmailProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var GiftCardEmailProcessor
     */
    private $emailProcessor;

    /**
     * @var EmailSender|MockObject
     */
    private $emailSender;

    /**
     * @var WriteInterface
     */
    private $mediaWriter;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->emailSender = $this->createPartialMock(EmailSender::class, ['sendEmail']);
        $this->mediaWriter = $this->objectManager->create(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $this->emailProcessor = $this->objectManager->create(
            GiftCardEmailProcessor::class,
            [
                'emailSender' => $this->emailSender
            ]
        );
    }

    /**
     * @dataProvider sendGiftCardEmailsByOrderItemDataProvider
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/order_with_giftcard_order_item.php
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_image.php
     * @magentoConfigFixture current_store amgiftcard/email/send_confirmation_to_sender 1
     */
    public function testSendGiftCardEmailsByOrderItem(array $productOptions, int $sendEmailCallsNum)
    {
        /** @var Image $image */
        $image = $this->objectManager->create(Image::class)->load('test_giftcard_image.jpg', 'image_path');
        $productOptions[GiftCardOptionInterface::IMAGE] = $image->getImageId();
        $amount = 50.0;
        $codes = ['TEST_CODE'];

        /** @var Item $orderItem */
        $orderItem = $this->objectManager->create(Item::class)->load('amgiftcard', 'product_type');
        $orderItem->setProductOptions($productOptions);

        $this->emailSender->expects($this->exactly($sendEmailCallsNum))->method('sendEmail');
        $this->emailProcessor->sendGiftCardEmailsByOrderItem($orderItem, $codes, $amount);

        $this->assertFileExists(
            $this->mediaWriter->getAbsolutePath(
                ImageBakingProcessor::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH . DIRECTORY_SEPARATOR . 'TEST_CODE.jpg'
            )
        );
    }

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_image.php
     */
    public function testSendGiftCardEmailByData()
    {
        $image = $this->objectManager->create(Image::class)->load('test_giftcard_image.jpg', 'image_path');
        $code = 'TEST_CODE';
        $amount = 50.0;
        $emailData = [
            GiftCardOptionInterface::IMAGE => $image->getImageId(),
            GiftCardOptionInterface::RECIPIENT_EMAIL => 'test@test.com'
        ];
        $this->emailSender->expects($this->once())->method('sendEmail');
        $this->emailProcessor->sendGiftCardEmailByData($emailData, $code, $amount);

        $this->assertFileExists(
            $this->mediaWriter->getAbsolutePath(
                ImageBakingProcessor::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH . DIRECTORY_SEPARATOR . 'TEST_CODE.jpg'
            )
        );
    }

    public function tearDown(): void
    {
        $this->mediaWriter->delete(
            ImageBakingProcessor::AMGIFTCARD_IMAGE_WITH_CODE_MEDIA_PATH . DIRECTORY_SEPARATOR . 'TEST_CODE.jpg'
        );
    }

    public function sendGiftCardEmailsByOrderItemDataProvider(): array
    {
        return [
            [
                [
                    GiftCardOptionInterface::RECIPIENT_EMAIL => 'test@test.com',
                ],
                1
            ],
            [
                [
                    GiftCardOptionInterface::RECIPIENT_EMAIL => 'test@test.com',
                    GiftCardOptionInterface::SENDER_EMAIL => 'sender@test.com'
                ],
                2
            ]
        ];
    }
}
