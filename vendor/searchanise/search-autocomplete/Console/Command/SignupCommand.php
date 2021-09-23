<?php

namespace Searchanise\SearchAutocomplete\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Signup Searchanise command
 */
class SignupCommand extends AbstractCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('searchanise:signup');
        $this->setDescription('Register searchanise.');

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
        return $this->setAdminArea()->signup($output);
    }
}
