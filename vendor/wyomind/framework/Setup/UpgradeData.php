<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Wyomind\Framework\Helper\Install;
use Wyomind\Framework\Helper\Module;

/**
 * Upgrade data for SImple Google Shopping
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Module
     */
    protected $module;
    /**
     * @var OutputInterface
     */
    protected $output;


    /**
     * @var null|Module
     */
    private $framework = null;

    /**
     * Recurring constructor.
     * @param Install $framework
     * @param Module $module
     * @param ConsoleOutput $output
     */
    public function __construct(
        Install $framework,
        Module $module,
        ConsoleOutput $output
    ) {
    
        $this->framework = $framework;
        $this->module = $module;
        $this->output = $output;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->module->updateConfigPubFolderEnabled($this->output);

    }
}
