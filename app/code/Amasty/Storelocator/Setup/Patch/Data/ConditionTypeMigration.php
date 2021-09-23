<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


declare(strict_types=1);

namespace Amasty\Storelocator\Setup\Patch\Data;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class ConditionTypeMigration implements DataPatchInterface, PatchVersionInterface
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
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(Location::TABLE_NAME),
            [LocationInterface::CONDITION_TYPE => 1],
            [
                'actions_serialized != ?' =>
                    '{"type":"Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition\\\\Combine",' .
                    '"attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}'
            ]
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
     * will skip if true: version_compare(call_user_func([$patchClassName, 'getVersion']), $dbVersion) <= 0
     * @return string
     */
    public static function getVersion()
    {
        return '2.4.6';
    }
}
