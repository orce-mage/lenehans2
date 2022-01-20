<?php

namespace StripeIntegration\Payments\Test\Integration\CLI\Upgrades;

class ConfigMigrationTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
    }

    /**
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/SampleModuleConfig.php
     */
    public function testConfigMigration()
    {
        $setup = $this->objectManager->get(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
        $migrate = $this->objectManager->get(\StripeIntegration\Payments\Helper\Migrate::class);
        $migrate->adminConfigElementsToCheckout($setup);

        $coreConfigData = $setup->getTable('core_config_data');
        $select = $setup->getConnection()->select()->from($coreConfigData)
            ->where('path like ?', 'payment/stripe_payments%');

        $settings = $setup->getConnection()->fetchAll($select);
        $migratedData = [];
        foreach ($settings as $setting)
            $migratedData[$setting['path']] = $setting['value'];

        $this->assertEquals(1, $migratedData['payment/stripe_payments/payment_flow']);
        $this->assertEquals("authorize", $migratedData['payment/stripe_payments_checkout/payment_action']);
        $this->assertEquals(1, $migratedData['payment/stripe_payments_checkout/automatic_invoicing']);
        $this->assertEquals(1, $migratedData['payment/stripe_payments_checkout/expired_authorizations']);
        $this->assertEquals(1, $migratedData['payment/stripe_payments_checkout/save_payment_method']);
    }
}
