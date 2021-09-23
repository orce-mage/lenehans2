<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Test\Integration\Controller;

class AccountActionsTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testIndexModuleDisabled()
    {
        $this->dispatch('/amgcard/account/index');

        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testAddCardModuleDisabled()
    {
        $this->dispatch('/amgcard/account/addcard/');

        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 0
     */
    public function testRemoveModuleDisabled()
    {
        $this->dispatch('/amgcard/account/remove/');

        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testIndexNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/index/');

        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testAddCardNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/addcard/');

        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * @magentoConfigFixture current_store amgiftcard/general/active 1
     */
    public function testRemoveNotLoggedIn()
    {
        $this->dispatch('/amgcard/account/remove/');

        $this->assertRedirect($this->stringContains('customer/account/login'));
    }
}
