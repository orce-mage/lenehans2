<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


declare(strict_types=1);

namespace Amasty\Storelocator\Setup\Patch\Data;

use Amasty\Storelocator\Model\ResourceModel\Location;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateConditionClass implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        /* this data will be prepared, parsed and replace condition class */
        $relationsSelect = $connection->select()->from(
            $this->moduleDataSetup->getTable(Location::TABLE_NAME)
        );
        $ruleRelationsDataSet = $connection->fetchAll($relationsSelect);
        foreach ($ruleRelationsDataSet as $locationRow) {
            $this->updateActionData($locationRow);
        }
    }

    /**
     * Update condition class in actions_serialized column
     *
     * @param $locationRow
     */
    public function updateActionData($locationRow)
    {
        // phpcs:ignore
        $oldConditionClass = 'Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition';
        // phpcs:ignore
        $newConditionClass = 'Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition';
        $modifiedData =
            str_replace($oldConditionClass, $newConditionClass, $locationRow['actions_serialized']);
        $connection = $this->moduleDataSetup->getConnection();
        $connection->update(
            $this->moduleDataSetup->getTable(Location::TABLE_NAME),
            ['actions_serialized' => $modifiedData],
            $connection->quoteInto('id = ?', $locationRow['id'])
        );
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '2.3.0';
    }
}
