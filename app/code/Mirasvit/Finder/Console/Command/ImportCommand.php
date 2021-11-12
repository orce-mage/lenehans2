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
use Mirasvit\Finder\Service\ImportOptionService;
use Mirasvit\Finder\Ui\Import\Form\Field\Mode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    private $finderRepository;

    private $importService;

    private $indexer;

    public function __construct(
        FinderRepository $finderRepository,
        ImportOptionService $importService,
        Indexer $indexer
    ) {
        $this->finderRepository = $finderRepository;
        $this->importService    = $importService;
        $this->indexer          = $indexer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('mirasvit:finder:import')
            ->setDescription('Import Finder Products')
            ->addUsage('1 /var/www/import.csv');

        $this->addArgument('finder_id', InputArgument::REQUIRED, 'Finder Id');
        $this->addArgument('file', InputArgument::REQUIRED, 'File Path');
        $this->addArgument('mode', InputArgument::OPTIONAL, 'Import Mode: allowed values: ' .
            Mode::MODE_OVERWRITE . ', ' . Mode::MODE_UPDATE, Mode::MODE_OVERWRITE);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $finderId    = (int)$input->getArgument('finder_id');
        $file        = (string)$input->getArgument('file');
        $isOverwrite = (string)$input->getArgument('mode') !== Mode::MODE_UPDATE;

        if ($finderId && $file) {

            if (!file_exists($file)) {
                throw new \InvalidArgumentException('Incorrect file name. File does not exist.');
            }

            $ts = microtime(true);

            $finder = $this->finderRepository->get($finderId);

            $this->importService->importFile($finder, $file, $isOverwrite);

            $output->writeln(sprintf(
                '<info>File successfully imported. (%s sec.)</info>',
                round(microtime(true) - $ts, 2)
            ));

            return;
        }

        $help = new HelpCommand();
        $help->setCommand($this);

        $help->run($input, $output);
    }
}
