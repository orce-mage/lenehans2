<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Console\Command\Operation;

use Amasty\ExportCore\Export\Run;
use Amasty\ExportCore\Model\Process\ProcessRepository;
use Magento\Framework\App\State;

/**
 * @codeCoverageIgnore
 */
class RunJob
{
    /**
     * @var Run
     */
    private $runner;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    public function __construct(
        ProcessRepository $processRepository,
        Run $runner,
        State $appState
    ) {
        $this->runner = $runner;
        $this->appState = $appState;
        $this->processRepository = $processRepository;
    }

    public function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        try {
            $process = $this->processRepository->getByIdentity($input->getArgument('identity'));

            //sometimes area code should be set
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                [$this->runner, 'execute'],
                [$process->getProfileConfig(), $process->getIdentity()]
            );
        } catch (\Exception $e) {
            $this->processRepository->markAsFailed(
                $input->getArgument('identity'),
                $e->getMessage()
            );
        }
    }
}
