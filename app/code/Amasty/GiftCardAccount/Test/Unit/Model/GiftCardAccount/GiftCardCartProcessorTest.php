<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountValidator;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Quote as GiftCardQuote;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor
 */
class GiftCardCartProcessorTest extends \PHPUnit\Framework\TestCase
{
    const ACCOUNT_ID = 1;
    const CARD_VALUE = 20;
    const CARD_CODE = 'test_code';

    /**
     * @var GiftCardCartProcessor
     */
    private $processor;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var GiftCardAccountValidator|MockObject
     */
    private $accountValidator;

    /**
     * @var QuoteRepository|MockObject
     */
    private $quoteRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->session = $this->createPartialMock(Session::class, ['getQuote']);
        $this->accountValidator = $this->createPartialMock(
            GiftCardAccountValidator::class,
            ['canApplyForQuote', 'validateCode']
        );
        $this->quoteRepository = $this->createPartialMock(QuoteRepository::class, ['save']);

        $this->processor = $objectManager->getObject(
            GiftCardCartProcessor::class,
            [
                'checkoutSession' => $this->session,
                'accountValidator' => $this->accountValidator,
                'quoteRepository' => $this->quoteRepository
            ]
        );
    }

    public function testApplyToCart()
    {
        $expected = [
            [
                GiftCardCartProcessor::GIFT_CARD_ID => self::ACCOUNT_ID,
                GiftCardCartProcessor::GIFT_CARD_CODE => self::CARD_CODE,
                GiftCardCartProcessor::GIFT_CARD_AMOUNT => self::CARD_VALUE,
                GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT => self::CARD_VALUE
            ]
        ];

        $account = $this->getAccount();
        $quote = $this->getQuoteWithExtensionAttributes();
        $quote->expects($this->once())->method('collectTotals');

        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->accountValidator->expects($this->once())->method('canApplyForQuote')
            ->with($account, $quote)
            ->willReturn(true);
        $this->accountValidator->expects($this->once())->method('validateCode')
            ->with($account, $quote)
            ->willReturn(true);
        $this->quoteRepository->expects($this->once())->method('save')->with($quote);

        $this->processor->applyToCart($account);
        $this->assertEquals($expected, $quote->getExtensionAttributes()->getAmGiftcardQuote()->getGiftCards());
    }

    public function testApplyToCartInvalidAccount()
    {
        $account = $this->getAccount();
        $quote = $this->createPartialMock(Quote::class, []);

        $this->accountValidator->expects($this->once())->method('canApplyForQuote')
            ->with($account, $quote)
            ->willReturn(false);
        $this->accountValidator->expects($this->once())->method('validateCode')
            ->with($account, $quote)
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->processor->applyToCart($account, $quote);
    }

    public function testApplyToCartExistingAccount()
    {
        $account = $this->getAccount();
        $quote = $this->getQuoteWithExtensionAttributes();
        $quote->getExtensionAttributes()->getAmGiftcardQuote()->setGiftCards(
            [
                [
                    GiftCardCartProcessor::GIFT_CARD_ID => self::ACCOUNT_ID
                ]
            ]
        );

        $this->accountValidator->expects($this->once())->method('canApplyForQuote')
            ->with($account, $quote)
            ->willReturn(true);
        $this->accountValidator->expects($this->once())->method('validateCode')
            ->with($account, $quote)
            ->willReturn(true);

        $this->expectExceptionMessage('This gift card account is already in the quote.');
        $this->processor->applyToCart($account, $quote);
    }

    public function testRemoveFromCart()
    {
        $account = $this->getAccount();
        $quote = $this->getQuoteWithExtensionAttributes();
        $quote->expects($this->once())->method('collectTotals');
        $quote->getExtensionAttributes()->getAmGiftcardQuote()->setGiftCards(
            [
                [
                    GiftCardCartProcessor::GIFT_CARD_ID => self::ACCOUNT_ID
                ]
            ]
        );
        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->quoteRepository->expects($this->atLeastOnce())->method('save')->with($quote);
        $this->processor->removeFromCart($account);
    }

    public function testRemoveFromCartNoAccount()
    {
        $account = $this->getAccount();
        $quote = $this->getQuoteWithExtensionAttributes();

        $this->expectExceptionMessage('Gift Card account wasn\'t found in the quote');
        $this->processor->removeFromCart($account, $quote);
    }

    /**
     * @return Account|MockObject
     */
    protected function getAccount()
    {
        $account = $this->createPartialMock(Account::class, []);
        $account->setAccountId(self::ACCOUNT_ID);
        $account->setCurrentValue(self::CARD_VALUE);

        $code = $this->createPartialMock(\Amasty\GiftCard\Model\Code\Code::class, []);
        $code->setCode(self::CARD_CODE);
        $account->setCodeModel($code);

        return $account;
    }

    /**
     * @return Quote|MockObject
     */
    protected function getQuoteWithExtensionAttributes()
    {
        $gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuote->setGiftCards([]);
        $extensionAttributes = $this->createPartialMock(CartExtension::class, ['getAmGiftcardQuote']);
        $extensionAttributes->expects($this->any())->method('getAmGiftcardQuote')->willReturn($gCardQuote);
        $quote = $this->createPartialMock(Quote::class, ['collectTotals', 'getExtensionAttributes']);
        $quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        return $quote;
    }
}
