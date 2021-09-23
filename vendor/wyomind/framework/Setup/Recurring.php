<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Wyomind\Framework\Helper\Install;
use Wyomind\Framework\Helper\Module;

/**
 * Class Recurring
 * @package Wyomind\Framework\Setup
 */
class Recurring implements InstallSchemaInterface
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
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    
        $files = [
            "Magento/Ui/TemplateEngine/Xhtml/Result.php"
        ];
        $this->framework->copyFilesByMagentoVersion(__FILE__, $files);
    }
}
