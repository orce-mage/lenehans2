<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateBatchTable
     */
    private $createBatchTable;

    /**
     * @var Operation\CreateProcessTable
     */
    private $createProcessTable;

    /**
     * @var Operation\CreateFileUploadMapTable
     */
    private $createFileUploadMapTable;

    public function __construct(
        Operation\CreateBatchTable $createBatchTable,
        Operation\CreateProcessTable $createProcessTable,
        Operation\CreateFileUploadMapTable $createFileUploadMapTable
    ) {
        $this->createBatchTable = $createBatchTable;
        $this->createProcessTable = $createProcessTable;
        $this->createFileUploadMapTable = $createFileUploadMapTable;
    }

    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->createBatchTable->execute($installer);
        $this->createProcessTable->execute($installer);
        $this->createFileUploadMapTable->execute($installer);

        $installer->endSetup();
    }
}
