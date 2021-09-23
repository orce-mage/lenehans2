<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Controller\Adminhtml\Account;

use Amasty\GiftCard\Model\CodePool\CodePool;
use Amasty\GiftCardAccount\Api\GiftCardAccountRepositoryInterface;
use Amasty\GiftCardAccount\Controller\Adminhtml\Account\Save;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Store\Model\StoreManagerInterface;

class SaveTest extends \Magento\TestFramework\TestCase\AbstractController
{
    const ACCOUNT_BALANCE = 50;

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/codepool_with_codes.php
     */
    public function testExecuteNewAccount()
    {
        /** @var CodePool $codePool */
        $codePool = $this->_objectManager->get(CodePool::class)->load('test_code_pool', 'title');

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore();

        $requestData = [
            'code_pool' => $codePool->getCodePoolId(),
            'status' => '1',
            'website_id' => $store->getWebsiteId(),
            'balance' => self::ACCOUNT_BALANCE,
            'expired_date' => ''
        ];
        $controller = $this->_objectManager->get(Save::class);
        $controller->getRequest()->setPostValue($requestData);
        $controller->execute();

        $this->assertSessionMessages($this->equalTo(['The code account has been saved.']));

        /** @var \Amasty\GiftCardAccount\Model\GiftCardAccount\Account $account */
        $account = $this->_objectManager->get(Repository::class)->getByCode('TEST_CODE_FREE');
        $this->assertEquals($account->getInitialValue(), self::ACCOUNT_BALANCE);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Amasty_GiftCardAccount::Test/Integration/_files/giftcard_account.php
     * @magentoConfigFixture current_store system/smtp/disable 1
     */
    public function testExecuteExistingAccountWithSendEmail()
    {
        $existingAccount = $this->_objectManager->get(GiftCardAccountRepositoryInterface::class)
            ->getByCode('TEST_CODE_USED');
        $requestData = [
            'account_id' => $existingAccount->getAccountId(),
            'current_value' => self::ACCOUNT_BALANCE,
            'recipient_name' => 'Test Recipient',
            'recipient_email' => 'test@test.com',
            'send' => '1'
        ];

        $controller = $this->_objectManager->get(Save::class);
        $controller->getRequest()->setPostValue($requestData);
        $controller->execute();

        $this->assertSessionMessages($this->equalTo([
            'The email has been sent successfully.', 'The code account has been saved.'
        ]));
        $this->assertTrue($existingAccount->isSent());
        $this->assertEquals($existingAccount->getCurrentValue(), self::ACCOUNT_BALANCE);
    }
}
