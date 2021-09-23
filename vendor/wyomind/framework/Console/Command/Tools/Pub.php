<?php


/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Console\Command\Tools;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wyomind\Framework\Helper\ModuleFactory;

/**
 * Class Activate
 * @package Wyomind\Framework\Console\Command\License
 */
class Pub extends Command
{
    /**
     * @var Module
     */
    protected $module;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var ConfigFactory
     */
    protected $configFactory;
    /**
     * @var ModuleFactory
     */
    protected $license;


    /**
     * LicenseAbstract constructor.
     * @param Module $module
     */
    public function __construct(
        ModuleFactory $module
    ) {
    

        parent::__construct();
        $this->module = $module;
    }

    public function configure()
    {
        $this->setName('wyomind:tools:pub')
            ->setDescription(__('Check if the pub folder must be used when generating urls'))
            ->setDefinition([]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $this->module->create()->updateConfigPubFolderEnabled($output);
        return Cli::RETURN_SUCCESS;

    }
}
