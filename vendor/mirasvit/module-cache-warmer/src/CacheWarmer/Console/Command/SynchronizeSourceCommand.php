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


use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\StateFactory;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Service\CrawlServiceFactory;
use Mirasvit\CacheWarmer\Service\SourceService;
use Mirasvit\CacheWarmer\Service\SessionServiceFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeSourceCommand extends \Symfony\Component\Console\Command\Command
{
    private $objectManager;

    private $appStateFactory;

    private $sourceService;

    private $crawlServiceFactory;

    private $sessionServiceFactory;

    private $customerCollectionFactory;

    private $pageRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        StateFactory $appStateFactory,
        SourceService $sourceService,
        CrawlServiceFactory $crawlServiceFactory,
        SessionServiceFactory $sessionServiceFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        PageRepositoryInterface $pageRepository
    ) {
        $this->objectManager             = $objectManager;
        $this->appStateFactory           = $appStateFactory;
        $this->sourceService             = $sourceService;
        $this->crawlServiceFactory       = $crawlServiceFactory;
        $this->sessionServiceFactory     = $sessionServiceFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->pageRepository            = $pageRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:cache-warmer:sync-source')
            ->setDescription('Synchronize sources with the warmer queue');

        $this->addOption('source-id', null, InputOption::VALUE_REQUIRED, 'Set source id');
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Reset synchronization date');
        $this->addOption('unlock', null, InputOption::VALUE_NONE, 'Unlock');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('unlock')) {
            $this->unlock();
        }

        if ($this->isLocked()) {
            $output->writeln('<comment>Current process is running or was finished incorrectly.</comment>');
            $output->writeln('To unlock run with option "--unlock"');

            return false;
        }

        $this->lock();
        $output->writeln('Start synchronization');

        try {
            $this->appStateFactory->create()->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $id = $input->getOption('source-id') ?: null;

        $sourceRepository = $this->sourceService->getSourceRepository();

        if($input->getOption('reset')) { // reset sources synchronization date
            $this->handelReset($output, $id);
            $this->unlock();

            return;
        }

        $parsed = $this->sourceService->exportUrls($id);

        foreach ($parsed as $sourceId => $sourceParsed) {
            $source = $sourceRepository->get($sourceId);
            $output->writeln('Syncronizing source "' . $source->getSourceName() . '" ...');

            $customerGroupIds = $source->getCustomerGroups();
            $counter          = 0;

            foreach ($customerGroupIds as $customerGroupId) {

                $output->writeln('Syncronizing for customer group ' . $customerGroupId . ' ...');

                $crawlService      = $this->crawlServiceFactory->create();
                $sessionDataCookie = false;

                if ($customerGroupId) {
                    $customersCollection = $this->customerCollectionFactory->create();
                    $customersCollection->addFieldToFilter('group_id', $customerGroupId);

                    // do not sync URLs for customer group that don't have customers
                    // Magento ignoring customer_group vary data for empty groups
                    // also prevens to add unused pages to the warmer's queue
                    if (!$customersCollection->count()) {
                        $output->writeln('No customers present in this group');
                        continue;
                    }

                    $sessionData = [
                        'customer_group'     => $customerGroupId,
                        'customer_logged_in' => 1,
                    ];

                    $sessionDataCookie = $this->sessionServiceFactory->create()->getSessionCookie($sessionData, 0, 0);
                }

                foreach ($sourceParsed as $url) {
                    if ($url == $sourceParsed['user_agent']) {
                        continue;
                    }

                    $result = $crawlService->makeRequest($url, $sessionDataCookie, $sourceParsed['user_agent']);
                    $crawlService->parseCookies($url, $result['response']->getBody());

                    $output->writeln($result['curl']);

                    $this->sourceService->resolveSource($url, $sourceId);
                    $counter++;
                }
            }

            $this->sourceService->cleanup($sourceParsed, $sourceId);

            $source->setLastSyncronizedAt(date("Y-m-d H:i:s"));
            $sourceRepository->save($source);

            $output->writeln('Syncronizing source "' . $source->getSourceName() . '" finished.');
            $output->writeln('Total synchronized urls: ' . $counter);
        }

        $output->writeln('Synchronization finished');
        $this->unlock();
    }

    /**
     * @param OutputInterface $output
     * @param int|null $sourceId
     */
    private function handelReset(OutputInterface $output, $sourceId = null)
    {
        $sourceRepository = $this->sourceService->getSourceRepository();

        if ($sourceId) {
            $source = $sourceRepository->get($sourceId);

            if (!$source) {
                $output->writeln('Source with ID ' . $sourceId . ' does not exist');
            } else {
                $output->writeln('Reseting source "' . $source->getSourceName() . '" ...');
                $this->resetSource($source);
            }
        } else {
            foreach ($sourceRepository->getCollection() as $source) {
                $output->writeln('Reseting source "' . $source->getSourceName() . '" ...');
                $this->resetSource($source);
            }
        }

        $output->writeln('Done!');
    }

    /**
     * @param SourceInterface $source
     */
    private function resetSource(SourceInterface $source)
    {
        $pageCollection = $this->pageRepository->getCollection()->addFieldToFilter('source_id', $source->getId());

        // if url from the removed source has visits - reassign page to the default source
        // otherwise - delete
        foreach ($pageCollection as $page) {
            if ($page->getPopularity() > 0) {
                $page->setSourceId(SourceInterface::DEFAULT_SOURCE_ID);
                $this->pageRepository->save($page);
            } else {
                $this->pageRepository->delete($page);
            }
        }

        $source->setLastSyncronizedAt(null);
        $this->sourceService->getSourceRepository()->save($source);
    }

    /**
     * @return void
     */
    private function unlock()
    {
        $lockFile = $this->getLockFile();
        if (is_file($lockFile)) {
            unlink($lockFile);
        }
    }

    /**
     * @return string
     */
    private function getLockFile()
    {
        $tmpPath  = $this->objectManager
            ->get(\Mirasvit\CacheWarmer\Model\Config::class)
            ->getTmpPath();
        $lockFile = $tmpPath . '/cache-warmer.cli.crawl.lock';

        return $lockFile;
    }

    /**
     * @return bool
     */
    private function isLocked()
    {
        $lockFile = $this->getLockFile();
        if (file_exists($lockFile)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function lock()
    {
        $lockFile = $this->getLockFile();

        $lockPointer = fopen($lockFile, "w");
        fwrite($lockPointer, date('c'));
        fclose($lockPointer);

        return true;
    }
}
