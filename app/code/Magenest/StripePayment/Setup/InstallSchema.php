<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_stripe_charge'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'charge_id',
                Table::TYPE_TEXT,
                30,
                ['nullable' => false],
                'Stripe Charge ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                10,
                ['nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Customer ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                15,
                [],
                'Charge Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                'null',
                ['default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Charge table');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_stripe_customer'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'magento_customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Magento Customer ID'
            )
            ->addColumn(
                'stripe_customer_id',
                Table::TYPE_TEXT,
                40,
                ['nullable' => false],
                'Stripe Customer ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                'null',
                ['default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Customer Table');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable($installer->getTable('magenest_stripe_product_attribute'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                [],
                'Attribute Value'
            )
            ->setComment('Product Attribute Value Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
