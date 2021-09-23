<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper\License;

use \Symfony\Component\Console\Input\ArrayInput;
use \Wyomind\Framework\Console\Command\License\Activate as Command;

/**
 * Class Update
 * @package Wyomind\Framework\Helper\License
 */
class Update extends \Wyomind\Framework\Helper\License
{

    /**
     * @var Command
     */
    public $command;
    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    public $output;

    /**
     * Update constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Symfony\Component\Console\Output\ConsoleOutput $output
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\Framework\Helper\ModuleFactory $license
     * @param \Wyomind\Framework\Model\ResourceModel\ConfigFactory $configFactory
     * @param \Magento\Framework\Module\Dir\ReaderFactory $directoryReaderFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context,
        \Symfony\Component\Console\Output\ConsoleOutput $output,
        \Magento\Framework\App\State $state,
        \Wyomind\Framework\Helper\ModuleFactory $license,
        \Wyomind\Framework\Model\ResourceModel\ConfigFactory $configFactory,
        \Magento\Framework\Module\Dir\ReaderFactory $directoryReaderFactory
    ) {
    
        $this->output = $output;
        $this->command = new  Command($state, $license, $configFactory);
        parent::__construct($objectManager, $context);
    }


    /**
     * @param $module
     * @param $context
     */
    public function update($class, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        try {
            $path = explode("\\", $class);
            $module = $path[1];

            $ak = $this->getStoreConfigUncrypted(strtolower($module) . "/license/activation_key");
            $currentVersion = $context->getVersion();
            $module = "Wyomind_" . $module;
            $newVersion = $this->getModuleVersion($module);


            if ($newVersion != $currentVersion && !empty($ak)) {
                $this->output->writeln("");
                $this->output->writeln("<comment>Re-activating the license...</comment>");

                $this->command->configure();
                $input = new ArrayInput([
                    "module" => $module,
                    "activation-key" => $ak,
                    "--auto-request" => 1
                ], $this->command->getDefinition());

                $this->command->execute($input, $this->output);
            }
        } catch (\Exception $e) {
            $this->output->writeln("<error>Unable to re-activate the license. " . $e->getMessage() . "</error>");
        }
    }
}
