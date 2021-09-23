<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Test\Integration\Controller\Preview;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Test\Integration\Traits\ImageUpload;
use Magento\TestFramework\Request;

class GetPreviewContentTest extends \Magento\TestFramework\TestCase\AbstractController
{
    use ImageUpload;

    const TEST_SENDER_NAME = 'Test Sender Name';

    /**
     * @magentoDataFixture Amasty_GiftCard::Test/Integration/_files/giftcard_product_open_amount.php
     * @magentoConfigFixture current_store amgiftcard/email/email_template amgiftcard_email_email_template
     */
    public function testExecute()
    {
        $image = $this->_objectManager->create(\Amasty\GiftCard\Model\Image\Image::class)
            ->load('test_giftcard_image.jpg', 'image_path');
        $requestData = [
            GiftCardOptionInterface::SENDER_NAME => self::TEST_SENDER_NAME,
            GiftCardOptionInterface::IMAGE => $image->getImageId(),
            GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT => 50
        ];
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setParams($requestData);
        $this->dispatch('/amgcard/preview/getpreviewcontent');

        $this->assertEquals(200, $this->getResponse()->getStatusCode());

        $content = $this->getResponse()->getContent();
        $this->assertTrue((bool)strpos($content, '$50'));
        $this->assertTrue((bool)strpos($content, self::TEST_SENDER_NAME));
    }
}
