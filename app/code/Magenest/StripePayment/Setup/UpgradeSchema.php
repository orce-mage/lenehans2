<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->dropColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'store_id'
            );
            $setup->getConnection()->dropColumn(
                $setup->getTable('magenest_stripe_product_attribute'),
                'attribute_id'
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $table = $setup->getConnection()->newTable($setup->getTable('magenest_stripe_card'))
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
                    [],
                    'Customer ID'
                )->addColumn(
                    'card_id',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Card ID'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    10,
                    ['default' => 'active'],
                    'Status'
                )
                ->addColumn(
                    'brand',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Card Brand'
                )
                ->addColumn(
                    'last4',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Card last 4 digit'
                )
                ->addColumn(
                    'exp_month',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Exp month'
                )
                ->addColumn(
                    'exp_year',
                    Table::TYPE_TEXT,
                    10,
                    [],
                    'Exp year'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    'null',
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('Card Table');

            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('magenest_stripe_card'),
                'threed_secure',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => '3d secure status'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $tableName = $setup->getTable('magenest_stripe_card');
            $columnName = 'three_d_secure';
            $connection = $setup->getConnection();
            if ($connection->tableColumnExists($tableName, $columnName) === true) {
                $setup->getConnection()->dropColumn($setup->getTable('magenest_stripe_card'), 'three_d_secure');
            }
            $setup->getConnection()->changeColumn(
                $setup->getTable('magenest_stripe_card'),
                'threed_secure',
                'three_d_secure',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'comment' => '3d secure status'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $this->alterStripeChargeTable($setup);
            $this->addSourceTable($setup);
        }

        if (version_compare($context->getVersion(), '2.2.1') < 0) {
            $this->alterStripeSourceTable($setup);
        }

        $setup->endSetup();
    }

    private function alterStripeSourceTable($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('magenest_stripe_source'),
                'quote_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'comment' => 'Quote id',
                    'nullable' => true,
                ]
            );
        $setup->getConnection()->changeColumn(
            $setup->getTable('magenest_stripe_source'),
            'order_id',
            'order_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 10,
                'nullable' => true,
                'comment' => 'Order ID'
            ]
        );
    }

    private function addSourceTable($setup)
    {
        $table = $setup->getConnection()->newTable($setup->getTable('magenest_stripe_source'))
            ->addColumn(
                'source_id',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'primary' => true],
                'Stripe Source ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                10,
                ['nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'method',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Method'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                'null',
                ['default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->setComment('Source table');

        $setup->getConnection()->createTable($table);
    }

    private function alterStripeChargeTable($setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('magenest_stripe_charge'),
            'method',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 50,
                'comment' => 'Payment method'
            ]
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('magenest_stripe_charge'),
            $setup->getIdxName(
                'magenest_stripe_charge',
                'charge_id'
            ),
            'charge_id'
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('magenest_stripe_charge'),
            'customer_id',
            'customer_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => null,
                'comment' => 'Customer ID',
                'nullable' => true,
            ]
        );
    }
}
