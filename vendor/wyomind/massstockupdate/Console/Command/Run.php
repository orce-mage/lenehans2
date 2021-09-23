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
 * $ bin/magento help wyomind:massstockupdate:run
 * Usage:
 * wyomind:massstockupdate:run [profile_id1] ... [profile_idN]
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
class Run extends Command
{
    /**
     *
     */
    const PROFILE_IDS_OPTION = "profile_ids";
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var string
     */
    public $name = "Mass Stock Update";
    /**
     * @var null|\Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory
     */
    public $_profilesCollectionFactory = null;

    /**
     * @var \Magento\Framework\App\State|null
     */
    public $_state = null;

    /**
     * Run constructor.
     * @param \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $profilesCollectionFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $profilesCollectionFactory,
        \Magento\Framework\App\State $state
    ) {
    
        $this->_state = $state;
        $this->_profilesCollectionFactory = $profilesCollectionFactory;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('wyomind:' . strtolower($this->module) . ':run')
            ->setDescription(__('Run ' . $this->name . ' profiles'))
            ->setDefinition(
                [
                    new \Symfony\Component\Console\Input\InputOption(
                        self::PROFILE_IDS_OPTION,
                        'p',
                        \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL | \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY,
                        __('List of profiles to run (comma separated)')
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
            $this->_state->setAreaCode('adminhtml');
            $profilesIds = array_filter($input->getOption(self::PROFILE_IDS_OPTION));

            if (!is_array($profilesIds) || !count($profilesIds)) {
                throw new \InvalidArgumentException('--profile_ids is empty. Please specify the profile to run.');
            }

            foreach (explode(",", $profilesIds[0]) as $profilesId) {
                $profile = $this->_profilesCollectionFactory->create()->getList($profilesId)->getFirstItem();
                $profile->multipleImport();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        return $returnValue;
    }
}
