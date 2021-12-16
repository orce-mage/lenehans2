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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends Command
{
    private $objectManager;

    private $appState;

    public function __construct(
        ObjectManagerInterface $objectManager,
        State $appState
    ) {
        $this->objectManager = $objectManager;
        $this->appState      = $appState;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:sorting:reindex')
            ->setDescription('Improved Sorting Reindex');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $this->reindexRankingFactor($output);
    }

    private function reindexRankingFactor(OutputInterface $output)
    {
        /** @var RankingFactorRepository $rankingFactorRepository */
        $rankingFactorRepository = $this->objectManager->create(RankingFactorRepository::class);

        foreach ($rankingFactorRepository->getCollection() as $rankingFactor) {

            $output->write(sprintf(
                'Reindex [Ranking Factor: %s] "%s"...',
                $rankingFactor->getId(),
                $rankingFactor->getName()
            ));

            $ts  = microtime(true);
            $mem = memory_get_usage();

            $this->getIndexer()->executeRankingFactor([$rankingFactor->getId()]);

            $output->writeln(sprintf(
                "<info>done</info> (%s / %s)",
                round(microtime(true) - $ts, 4) . 's',
                round((memory_get_usage() - $mem) / 1024 / 1024, 2) . 'Mb'
            ));
        }
    }

    private function getIndexer(): Indexer
    {
        return $this->objectManager->create(Indexer::class);
    }
}
