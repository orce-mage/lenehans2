<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterfaceFactory;
use Mirasvit\CacheWarmer\Api\Service\JobServiceInterfaceFactory;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;


    /**
     * @var JobRepositoryInterfaceFactory
     */
    private $jobRepositoryFactory;

    /**
     * @var JobServiceInterfaceFactory
     */
    private $jobServiceFactory;

    /**
     * @var PageRepositoryInterfaceFactory
     */
    private $pageRepositoryFactory;
    /**
     * @var \Mirasvit\CacheWarmer\Service\StatusServiceFactory
     */
    private $statusServiceFactory;
    /**
     * @var State
     */
    private $appState;

    /**
     * Command constructor.
     * @param JobRepositoryInterfaceFactory $jobRepositoryFactory
     * @param JobServiceInterfaceFactory $jobServiceFactory
     * @param PageRepositoryInterfaceFactory $pageRepositoryFactory
     * @param \Mirasvit\CacheWarmer\Service\StatusServiceFactory $statusServiceFactory
     * @param ObjectManagerInterface $objectManager
     * @param State $appState
     */
    public function __construct(
        JobRepositoryInterfaceFactory $jobRepositoryFactory,
        JobServiceInterfaceFactory $jobServiceFactory,
        PageRepositoryInterfaceFactory $pageRepositoryFactory,
        \Mirasvit\CacheWarmer\Service\StatusServiceFactory $statusServiceFactory,
        ObjectManagerInterface $objectManager,
        State $appState
    ) {
        $this->jobRepositoryFactory  = $jobRepositoryFactory;
        $this->jobServiceFactory     = $jobServiceFactory;
        $this->pageRepositoryFactory = $pageRepositoryFactory;
        $this->statusServiceFactory = $statusServiceFactory;
        $this->objectManager  = $objectManager;
        $this->appState  = $appState;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:cache-warmer')
            ->setDescription('Various commands');

        $this->addOption('warm', null, null, 'Create and Run new warmer job');
        $this->addOption('status', null, null, 'Update cache status');
        $this->addOption('remove-all-pages', null, null, 'Remove all pages');
        $this->addOption('ids', null, InputOption::VALUE_REQUIRED);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $e) {
        }

        $jobRepository = $this->jobRepositoryFactory->create();
        $pageRepository = $this->pageRepositoryFactory->create();
        $jobService = $this->jobServiceFactory->create();
        if ($input->getOption('warm')) {

            $pageIds = $input->getOption('ids') ? explode(',', $input->getOption('ids')) : [];

            $job = $jobRepository->create();
            $jobRepository->save($job);

            $output->writeln("Job #{$job->getId()} was scheduled");
            $jobService->run($job, 'manual', $pageIds);
            $output->writeln("Job was finished with status `{$job->getStatus()}`");
        } elseif ($input->getOption('status')) {
            $statusService = $this->statusServiceFactory->create();
            $statusService->runFullStatusUpdate();
        } elseif ($input->getOption('remove-all-pages')) {
            $collection = $pageRepository->getCollection();
            foreach ($collection as $page) {
                $pageRepository->delete($page);
            }

            $output->writeln('done');
        } else {
            $help = new HelpCommand();
            $help->setCommand($this);

            return $help->run($input, $output);
        }
    }
}
