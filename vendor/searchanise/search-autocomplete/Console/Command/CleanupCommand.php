<?php

namespace Searchanise\SearchAutocomplete\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleanup Searchanise command
 */
class CleanupCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('searchanise:cleanup');
        $this->setDescription('Cleanup registration data');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->setAdminArea()->cleanup($output);
    }
}
