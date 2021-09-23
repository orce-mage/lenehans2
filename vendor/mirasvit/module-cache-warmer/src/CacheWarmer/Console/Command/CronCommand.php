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

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * CronCommand constructor.
     * @param State $appState
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        State $appState,
        ObjectManagerInterface $objectManager
    ) {
        $this->appState      = $appState;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:cache-warmer:cron')
            ->setDescription('Test cache warmer cron functionality');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $jobs = [
            \Mirasvit\CacheWarmer\Cron\RateCron::class,
            \Mirasvit\CacheWarmer\Cron\UpdatePagesCron::class,
            \Mirasvit\CacheWarmer\Cron\WarmJobCron::class,
        ];

        foreach ($jobs as $job) {
            $obj = $this->objectManager->get($job);
            $output->write("Running $job ....");
            $obj->execute();
            $output->writeln("<info>completed</info>");
        }
    }
}
