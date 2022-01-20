<?php

namespace StripeIntegration\Payments\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $defaultConnection = $setup->getConnection();

        $this->dropTable($setup, 'stripe_customers');
        $this->dropTable($setup, 'stripe_payment_intents');
        $this->dropTable($setup, 'stripe_invoices');
        $this->dropTable($setup, 'stripe_coupons');
        $this->dropTable($setup, 'stripe_webhooks');
        $this->dropTable($setup, 'stripe_sources');
        $this->dropTable($setup, 'stripe_subscriptions');
        $this->dropTable($setup, 'stripe_checkout_sessions');

        $defaultConnection->delete(
            $this->getTableNameWithPrefix($setup, 'core_config_data'),
            "path LIKE 'payment/stripe_payments%'"
        );

        $eavSetup = $this->eavSetupFactory->create();
        $entityTypeId = 4; // \Magento\Catalog\Model\Product::ENTITY
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_enabled');
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_interval');
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_interval_count');
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_trial');
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_initial_fee');
        $eavSetup->removeAttribute($entityTypeId, 'stripe_sub_enabled');

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     */
    private function dropTable(SchemaSetupInterface $setup, $tableName)
    {
        $connection = $setup->getConnection();
        $connection->dropTable($this->getTableNameWithPrefix($setup, $tableName));
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     *
     * @return string
     */
    private function getTableNameWithPrefix(SchemaSetupInterface $setup, $tableName)
    {
        return $setup->getTable($tableName);
    }
}
