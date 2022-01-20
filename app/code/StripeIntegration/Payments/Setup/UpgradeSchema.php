<?php

namespace StripeIntegration\Payments\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.5.2') < 0)
        {
            $this->createWebhooksTable($setup);
        }

        if (version_compare($context->getVersion(), '1.8.0') < 0)
        {
            $this->createSourcesTable($setup);
        }

        if (version_compare($context->getVersion(), '1.8.8') < 0)
        {
            $this->createSubscriptionsTable($setup);
        }

        if (version_compare($context->getVersion(), '2.1.0') < 0)
        {
            $this->createCouponsTable($setup);
        }

        if (version_compare($context->getVersion(), '2.2.0') < 0)
        {
            $this->alterCustomersTable($setup);
            $this->createInvoicesTable($setup);
        }

        if (version_compare($context->getVersion(), '2.3.0') < 0)
        {
            $this->createPaymentIntentsTable($setup);
        }

        if (version_compare($context->getVersion(), '2.4.0') < 0)
        {
            $this->addSessionIndex($setup);
        }

        if (version_compare($context->getVersion(), '2.4.1') < 0)
        {
            $this->alterPaymentIntentsTable($setup);
        }

        if (version_compare($context->getVersion(), '2.8.0') < 0)
        {
            $this->createCheckoutSessionsTable($setup);
        }

        $setup->endSetup();
    }

    public function alterPaymentIntentsTable($setup)
    {
        $connection = $setup->getConnection();

        $connection->addColumn(
                $setup->getTable('stripe_payment_intents'),
                'customer_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'default' => 0,
                    'nullable' => false,
                    'unsigned' => true,
                    'comment' => 'Magento Customer ID'
                ]
            );

        $connection->addColumn(
                $setup->getTable('stripe_payment_intents'),
                'order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'default' => null,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Order ID'
                ]
            );

        $connection->addColumn(
                $setup->getTable('stripe_payment_intents'),
                'pm_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Payment Method ID'
                ]
            );

        $connection->addIndex(
                $setup->getTable('stripe_payment_intents'),
                $setup->getIdxName('stripe_payment_intents', ['order_id']),
                ['order_id']
            );

        $connection->addIndex(
                $setup->getTable('stripe_payment_intents'),
                $setup->getIdxName('stripe_payment_intents', ['customer_id']),
                ['customer_id']
            );

        $connection->addIndex(
                $setup->getTable('stripe_payment_intents'),
                $setup->getIdxName('stripe_payment_intents', ['pm_id']),
                ['pm_id']
            );
    }

    public function addSessionIndex($setup)
    {
        $connection = $setup->getConnection();

        $connection->addIndex(
            $setup->getTable('stripe_customers'),
            $setup->getIdxName('stripe_customers', ['session_id']),
            ['session_id']
        );
    }

    public function createPaymentIntentsTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_payment_intents')
            )->addColumn(
                'pi_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => true, 'unique' => true],
                'Payment Intent ID'
            )->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Quote ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Order Increment ID'
            )->addColumn(
                'invoice_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Invoice ID'
            )->addIndex(
                $setup->getIdxName('stripe_payment_intents', ['pi_id']),
                ['pi_id']
            )->addIndex(
                $setup->getIdxName('stripe_payment_intents', ['quote_id']),
                ['quote_id']
            )->addIndex(
                $setup->getIdxName('stripe_payment_intents', ['order_increment_id']),
                ['order_increment_id']
            );

        $setup->getConnection()->createTable($table);
    }

    public function createInvoicesTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_invoices')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'invoice_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Invoice ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order Increment ID'
            )->addIndex(
                $setup->getIdxName('stripe_invoices', ['invoice_id']),
                ['invoice_id']
            )->addIndex(
                $setup->getIdxName('stripe_invoices', ['order_increment_id']),
                ['order_increment_id']
            );

        $setup->getConnection()->createTable($table);
    }

    public function createCouponsTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_coupons')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Sales Rule ID'
            )->addColumn(
                'coupon_duration',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Coupon Duration'
            )->addColumn(
                'coupon_months',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Number of months'
            );

        $setup->getConnection()->createTable($table);
    }

    public function createWebhooksTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_webhooks')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'config_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => \StripeIntegration\Payments\Helper\WebhooksSetup::VERSION],
                'Webhooks Configuration Version'
            )->addColumn(
                'webhook_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Webhook ID'
            )->addColumn(
                'publishable_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe API Publishable Key'
            )->addColumn(
                'store_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Store Code'
            )->addColumn(
                'live_mode',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Live Mode'
            )->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Active'
            )->addColumn(
                'last_event',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => 0],
                'Timestamp of last received event'
            )->addColumn(
                'api_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Stripe API Version'
            )->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2000,
                ['nullable' => true],
                'Webhook URL'
            )->addColumn(
                'api_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Stripe API Version'
            )->addColumn(
                'enabled_events',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10000,
                ['nullable' => true],
                'Enabled Webhook Events'
            )->addColumn(
                'connect',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Connected Accounts'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            );
        $setup->getConnection()->createTable($table);
    }

    public function createSourcesTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_sources')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'source_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Source ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Order Increment ID'
            )->addColumn(
                'stripe_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Stripe Customer ID'
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['source_id']),
                ['source_id']
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['order_increment_id']),
                ['order_increment_id']
            )->addIndex(
                $setup->getIdxName('stripe_sources', ['stripe_customer_id']),
                ['stripe_customer_id']
            );

        $setup->getConnection()->createTable($table);
    }

    public function createSubscriptionsTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_subscriptions')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Store ID'
            )->addColumn(
                'livemode',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Stripe API Mode'
            )->addColumn(
                'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe Subscription ID'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Order Increment ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Product ID'
            )->addColumn(
                'magento_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true, 'default' => 0],
                'Magento Customer ID'
            )->addColumn(
                'stripe_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stripe Customer ID'
            )->addColumn(
                'payment_method_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payment Method ID'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2048,
                ['nullable' => false],
                'Subscription Name'
            )->addColumn(
                'quantity',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 1],
                'Subscription Quantity'
            )->addColumn(
                'currency',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Currency Code'
            )->addColumn(
                'grand_total',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,4',
                ['unsigned' => false, 'nullable' => false],
                'Grand Total'
            )->addColumn(
                'is_new',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Subscription Just Created?'
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['subscription_id']),
                ['subscription_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['store_id']),
                ['store_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['livemode']),
                ['livemode']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['stripe_customer_id']),
                ['stripe_customer_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['magento_customer_id']),
                ['magento_customer_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['product_id']),
                ['product_id']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['created_at']),
                ['created_at']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['is_new']),
                ['is_new']
            )->addIndex(
                $setup->getIdxName('stripe_subscriptions', ['order_increment_id']),
                ['order_increment_id']
            );

        $setup->getConnection()->createTable($table);
    }

    public function alterCustomersTable($setup)
    {
        $connection = $setup->getConnection();

        $connection->addColumn(
            $setup->getTable('stripe_customers'),
            'pk',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'Stripe Account Public Key'
            ]
        );

        $connection->addIndex(
                $setup->getTable('stripe_customers'),
                $setup->getIdxName('stripe_customers', ['pk']),
                ['pk']
            );

        $connection->addIndex(
                $setup->getTable('stripe_customers'),
                $setup->getIdxName('stripe_customers', ['stripe_id']),
                ['stripe_id']
            );

        $connection->addIndex(
                $setup->getTable('stripe_customers'),
                $setup->getIdxName('stripe_customers', ['customer_id']),
                ['customer_id']
            );

        $connection->addIndex(
                $setup->getTable('stripe_customers'),
                $setup->getIdxName('stripe_customers', ['customer_email']),
                ['customer_email']
            );
    }

    public function createCheckoutSessionsTable($setup)
    {
        $table = $setup->getConnection()->newTable(
                $setup->getTable('stripe_checkout_sessions')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Quote Id'
            )->addColumn(
                'order_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Order Increment Id'
            )->addColumn(
                'checkout_session_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Checkout Session ID'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addIndex(
                $setup->getIdxName('stripe_checkout_sessions', ['quote_id']),
                ['quote_id']
            )->addIndex(
                $setup->getIdxName('stripe_checkout_sessions', ['checkout_session_id']),
                ['checkout_session_id']
            );

        $setup->getConnection()->createTable($table);
    }
}
