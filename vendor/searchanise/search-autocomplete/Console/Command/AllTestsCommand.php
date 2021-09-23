<?php

namespace Searchanise\SearchAutocomplete\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Perform full Searchanise test
 */
class AllTestsCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('searchanise:tests:all');
        $this->setDescription('Searchanise full testing');

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
        return $this->fullTest($output);
    }
}
