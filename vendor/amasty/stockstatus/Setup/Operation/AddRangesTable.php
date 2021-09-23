<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Model\ResourceModel\Range as RangeResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddRangesTable
{
    /**
     * @var GetRangeTable
     */
    private $getRangeTable;

    public function __construct(GetRangeTable $getRangeTable)
    {
        $this->getRangeTable = $getRangeTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {

        $table = $this->getRangeTable->execute($setup, RangeResource::MAIN_TABLE);

        $table->addIndex(
            $setup->getIdxName(
                RangeResource::MAIN_TABLE,
                [
                    RangeInterface::RULE_ID,
                    RangeInterface::FROM,
                    RangeInterface::TO
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                RangeInterface::RULE_ID,
                RangeInterface::FROM,
                RangeInterface::TO
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($table);
    }
}
