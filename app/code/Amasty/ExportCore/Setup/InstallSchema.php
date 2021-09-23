<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateProcessTable
     */
    private $createProcessTable;

    public function __construct(
        Operation\CreateProcessTable $createProcessTable
    ) {
        $this->createProcessTable = $createProcessTable;
    }

    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->createProcessTable->execute($installer);

        $installer->endSetup();
    }
}
