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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Console\Command;

use Mirasvit\Finder\Model\Index\Indexer;
use Mirasvit\Finder\Repository\FinderRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCommand extends Command
{
    private $finderRepository;

    private $indexer;

    public function __construct(
        FinderRepository $finderRepository,
        Indexer $indexer
    ) {
        $this->finderRepository = $finderRepository;
        $this->indexer          = $indexer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('mirasvit:finder:reindex')
            ->setDescription('Reindex Finder Products');

        $this->addArgument('id', InputArgument::OPTIONAL, 'Finder Id');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        foreach ($this->finderRepository->getCollection() as $finder) {
            if ($input->getArgument('id')
                && (int)$input->getArgument('id') !== $finder->getId()) {
                $output->writeln(sprintf(
                    'Skip [%s] "%s"',
                    $finder->getId(),
                    $finder->getName()
                ));

                continue;
            }

            $output->write(sprintf(
                'Reindex [%s] "%s"...',
                $finder->getId(),
                $finder->getName()
            ));

            $ts  = microtime(true);
            $mem = memory_get_usage();

            $this->indexer->reindex($finder);

            $output->writeln(sprintf(
                "<info>done</info> (%s / %s)",
                round(microtime(true) - $ts, 4) . 's',
                round((memory_get_usage() - $mem) / 1024 / 1024, 2) . 'Mb'
            ));
        }
    }
}
