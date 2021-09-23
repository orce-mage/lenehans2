<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardAccount;

use Amasty\GiftCard\Model\CodePool\CodePoolRule;
use Amasty\GiftCard\Model\CodePool\Repository;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Quote as GiftCardQuote;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator
 */
class GiftCardAccountValidatorTest extends \PHPUnit\Framework\TestCase
{
    const WEBSITE_ID = 0;
    const CODE = 'test_code';
    const CODE_POOL_ID = 1;
    const ALLOWED_SUBTOTAL = 50;
    const CUSTOMER_ID = 1;

    /**
     * @var GiftCardAccountValidator
     */
    private $accountValidator;

    /**
     * @var AllowedTotalCalculator|MockObject
     */
    private $totalCalculator;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var ConfigProvider|MockObject
     */
    private $config;

    /**
     * @var Repository|MockObject
     */
    private $codePoolRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->totalCalculator = $this->createPartialMock(AllowedTotalCalculator::class, ['getAllowedSubtotal']);
        $this->session = $this->createPartialMock(Session::class, ['isLoggedIn', 'getCustomerId']);
        $this->session->expects($this->any())->method('getCustomerId')->willReturn(self::CUSTOMER_ID);
        $this->config = $this->createPartialMock(
            ConfigProvider::class,
            ['isAllowUseThemselves', 'getAllowedProductTypes', 'isEnabled']
        );
        $this->codePoolRepository = $this->createPartialMock(Repository::class, ['getRuleByCodePoolId']);

        $this->accountValidator = $objectManager->getObject(
            GiftCardAccountValidator::class,
            [
                'storeManager' => $this->getStoreManager(),
                'allowedTotalCalculator' => $this->totalCalculator,
                'configProvider' => $this->config,
                'customerSession' => $this->session,
                'codePoolRepository' => $this->codePoolRepository
            ]
        );
    }

    /**
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($accountId, $websiteId, $status, $value, $errorMessage)
    {
        $code = $this->createPartialMock(\Amasty\GiftCard\Model\Code\Code::class, []);
        $code->setCode(self::CODE);

        $account = $this->createPartialMock(Account::class, []);
        $account->setCodeModel($code);
        $account->setAccountId($accountId);
        $account->setWebsiteId($websiteId);
        $account->setStatus($status);
        $account->setCurrentValue($value);

        $this->expectExceptionMessage($errorMessage);
        $this->accountValidator->validate($account);
    }

    public function testCanApplyForQuoteDiscountReached()
    {
        $account = $this->getValidAccount();

        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes']);
        $extensionAttributes = $this->createPartialMock(CartExtension::class, ['getAmGiftcardQuote']);

        $gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuote->setGiftAmountUsed(self::ALLOWED_SUBTOTAL);

        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardQuote')
            ->willReturn($gCardQuote);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->totalCalculator->expects($this->any())->method('getAllowedSubtotal')
            ->willReturn(self::ALLOWED_SUBTOTAL);

        $this->expectExceptionMessage('Gift card can\'t be applied. Maximum discount reached.');
        $this->accountValidator->canApplyForQuote($account, $quote);
    }

    public function testCanApplyForQuoteCustomer()
    {
        $account = $this->getValidAccount();
        $account->setCustomerCreatedId(self::CUSTOMER_ID);

        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes']);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn(null);

        $this->session->expects($this->atLeastOnce())->method('isLoggedIn')->willReturn(true);
        $this->config->expects($this->once())->method('isAllowUseThemselves')->willReturn(false);

        $this->expectExceptionMessage('Please be aware that it is not possible to use'
            . ' the gift card you purchased for your own orders.');
        $this->accountValidator->canApplyForQuote($account, $quote);
    }

    public function testCanApplyForQuoteValid()
    {
        $account = $this->getValidAccount();
        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes']);
        $quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn(null);
        $this->session->expects($this->atLeastOnce())->method('isLoggedIn')->willReturn(false);

        $this->assertTrue($this->accountValidator->canApplyForQuote($account, $quote));
    }

    public function testValidateCode()
    {
        $account = $this->getValidAccount();
        $address = $this->createPartialMock(Address::class, []);
        $quoteItem = $this->createPartialMock(Item::class, ['getAddress']);
        $quoteItem->expects($this->any())->method('getAddress')->willReturn($address);
        $quote = $this->createPartialMock(Quote::class, ['getAllVisibleItems']);
        $quote->expects($this->atLeastOnce())->method('getAllVisibleItems')
            ->willReturn([$quoteItem]);

        $codePoolRule = $this->createPartialMock(CodePoolRule::class, ['getConditions', 'validate']);
        $codePoolRule->expects($this->any())->method('validate')->with($address)->willReturn(true);
        $this->codePoolRepository->expects($this->once())->method('getRuleByCodePoolId')
            ->with(self::CODE_POOL_ID)
            ->willReturn($codePoolRule);

        $this->assertTrue($this->accountValidator->validateCode($account, $quote));
    }

    public function testValidateCodeNoQuoteItems()
    {
        $account = $this->getValidAccount();
        $quote = $this->createPartialMock(Quote::class, ['getAllVisibleItems']);
        $quote->expects($this->atLeastOnce())->method('getAllVisibleItems')
            ->willReturn([]);

        $codePoolRule = $this->createPartialMock(CodePoolRule::class, ['getConditions', 'validate']);
        $this->codePoolRepository->expects($this->once())->method('getRuleByCodePoolId')
            ->with(self::CODE_POOL_ID)
            ->willReturn($codePoolRule);

        $this->assertFalse($this->accountValidator->validateCode($account, $quote));
    }

    public function testValidateCodeNoRule()
    {
        $account = $this->getValidAccount();
        $quote = $this->createPartialMock(Quote::class, []);

        $this->codePoolRepository->expects($this->once())->method('getRuleByCodePoolId')
            ->with(self::CODE_POOL_ID)
            ->willReturn(null);

        $this->assertTrue($this->accountValidator->validateCode($account, $quote));
    }

    /**
     * @dataProvider isGiftCardApplicableToCartDataProvider
     */
    public function testIsGiftCardApplicableToCart($enabled, $productType, $expected)
    {
        $quoteItem = $this->createPartialMock(Item::class, ['getProductType', 'getOptions']);
        $quoteItem->expects($this->any())->method('getProductType')->willReturn($productType);
        $quoteItem->expects($this->any())->method('getOptions')->willReturn([]);
        $quote = $this->createPartialMock(Quote::class, ['getAllItems']);
        $quote->expects($this->any())->method('getAllItems')->willReturn([$quoteItem]);

        $this->config->expects($this->once())->method('isEnabled')->willReturn($enabled);
        $this->config->expects($this->once())->method('getAllowedProductTypes')->willReturn(
            ['simple']
        );
        $this->assertEquals($expected, $this->accountValidator->isGiftCardApplicableToCart($quote));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [0, self::WEBSITE_ID, 1, 10, 'Wrong gift card code'],
            [1, 2, 1, 10, 'Wrong gift card website'],
            [1, self::WEBSITE_ID, AccountStatus::STATUS_EXPIRED, 10, 'Gift card ' . self::CODE . ' is expired.'],
            [1, self::WEBSITE_ID, AccountStatus::STATUS_USED, 10, 'Gift card ' . self::CODE . ' is used.'],
            [1, self::WEBSITE_ID, AccountStatus::STATUS_INACTIVE, 10, 'Gift card ' . self::CODE . ' is not enabled.'],
            [
                1,
                self::WEBSITE_ID,
                AccountStatus::STATUS_ACTIVE,
                0,
                'Gift card ' . self::CODE . ' balance does not have funds.'
            ]
        ];
    }

    /**
     * @return array
     */
    public function isGiftCardApplicableToCartDataProvider()
    {
        return [
            [false, '', false],//disabled module
            [true, 'simple', true],//valid product
            [true, 'other', false]//invalid product
        ];
    }

    /**
     * @return StoreManager|MockObject
     */
    protected function getStoreManager()
    {
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, []);
        $website->setId(self::WEBSITE_ID);
        $storeManager = $this->createPartialMock(StoreManager::class, ['getWebsite']);
        $storeManager->expects($this->any())->method('getWebsite')
            ->willReturn($website);

        return $storeManager;
    }

    /**
     * @return Account|MockObject
     */
    protected function getValidAccount()
    {
        $account = $this->createPartialMock(Account::class, []);
        $account->setAccountId(1);
        $account->setWebsiteId(self::WEBSITE_ID);
        $account->setStatus(AccountStatus::STATUS_ACTIVE);
        $account->setCurrentValue(10);

        $code = $this->createPartialMock(\Amasty\GiftCard\Model\Code\Code::class, []);
        $code->setCode(self::CODE);
        $code->setCodePoolId(self::CODE_POOL_ID);
        $account->setCodeModel($code);

        return $account;
    }
}
