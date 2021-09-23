<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * $ bin/magento help wyomind:MassSockUpdate:run
 * Usage:
 * wyomind:MassSockUpdate:sql --id=profile_id --indexes=[profile_idN]
 *
 * Arguments:
 * profile_id            Space-separated list of import profiles (run all profiles if empty)
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class Sql extends Command
{

    /**
     * Command line option: --profile=1
     */
    const PROFILE_ID_OPTION = 'profile_id';
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var string
     */
    public $name = "Mass Stock Update";
    /**
     * @var null|\Wyomind\MassStockUpdate\Model\ProfilesFactory
     */
    public $_profilesFactory = null;

    /**
     * @var \Magento\Framework\App\State|null
     */
    public $_state = null;

    /**
     * Sql constructor.
     * @param \Wyomind\MassStockUpdate\Model\ProfilesFactory $profilesFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Wyomind\MassStockUpdate\Model\ProfilesFactory $profilesFactory,
        \Magento\Framework\App\State $state
    ) {
    
        $this->_state = $state;
        $this->_profilesFactory = $profilesFactory;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('wyomind:' . strtolower($this->module) . ':sql')
            ->setDescription(__('Run the sql files for ' . $this->name . ' profiles'))
            ->setDefinition(
                [
                    new \Symfony\Component\Console\Input\InputOption(
                        self::PROFILE_ID_OPTION,
                        'p',
                        \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                        __('Profile id')
                    )
                ]
            );
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
    

        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        try {
            $profilesId = $input->getOption(self::PROFILE_ID_OPTION);


            if ($profilesId == null) {
                throw new \InvalidArgumentException('--profile_id is a required option.');
            }

            $this->_state->setAreaCode('adminhtml');

            $profiles = $this->_profilesFactory->create();

            $profile = $profiles->load($profilesId);


            $profile->executeSqlFile();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }


        return $returnValue;
    }
}
