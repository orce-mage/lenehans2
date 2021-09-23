<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class RunJob extends Command
{
    /**
     * @var Operation\RunJob
     */
    private $runJob;

    public function __construct(
        Operation\RunJob $runJob,
        string $name = null
    ) {
        parent::__construct($name);
        $this->runJob = $runJob;
    }

    protected function configure()
    {
        if (method_exists($this, 'setHidden')) { // Compatibility fix for M2.2 and older
            $this->setHidden(true);
        }
        $this->setName('amasty:import:run-job');

        $this->setDefinition(
            [
                new InputArgument(
                    'identity',
                    InputArgument::REQUIRED
                ),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runJob->execute($input, $output);
    }
}
