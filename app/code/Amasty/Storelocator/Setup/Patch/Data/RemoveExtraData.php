<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


declare(strict_types=1);

namespace Amasty\Storelocator\Setup\Patch\Data;

use Amasty\Storelocator\Model\Import\Location as LocationImport;
use Amasty\Storelocator\Model\ResourceModel\Location;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class RemoveExtraData implements DataPatchInterface, PatchVersionInterface
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
        $locationTable = $this->moduleDataSetup->getTable(Location::TABLE_NAME);
        $storeAttributeTable = $this->moduleDataSetup->getTable(LocationImport::TABLE_AMASTY_STORE_ATTRIBUTE);
        $subQuery = sprintf('SELECT id FROM %s', $locationTable);
        $sql = sprintf(
            'DELETE FROM %s WHERE store_id NOT IN(%s);',
            $storeAttributeTable,
            $subQuery
        );

        $this->moduleDataSetup->getConnection()->query($sql);
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
        return '1.10.0';
    }
}
