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
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Service\Rate\CacheCoverageRateService;
use Mirasvit\CacheWarmer\Service\Rate\CacheFillRateService;
use Mirasvit\CacheWarmer\Service\Rate\ServerLoadRateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * TestCommand constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:cache-warmer:test')
            ->setDescription('Test cache warmer functionality');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CacheCoverageRateService $cacheCoverageRateService */
        $cacheCoverageRateService = $this->objectManager->create(CacheCoverageRateService::class);

        /** @var CacheFillRateService $cacheFillRateService */
        $cacheFillRateService = $this->objectManager->create(CacheFillRateService::class);

        /** @var ServerLoadRateService $serverLoadRateService */
        $serverLoadRateService = $this->objectManager->create(ServerLoadRateService::class);

        $output->writeln("Cache Coverage Rate: {$cacheCoverageRateService->getRate()}");
        $output->writeln("Cache Fill Rate: {$cacheFillRateService->getRate()}");
        $output->writeln("Server Load Rate: {$serverLoadRateService->getRate()}");

        /** @var \Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface $pageRepository */
        $pageRepository = $this->objectManager->create('Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface');

        /** @var \Mirasvit\CacheWarmer\Api\Service\PageServiceInterface $pageService */
        $pageService = $this->objectManager->create('Mirasvit\CacheWarmer\Api\Service\PageServiceInterface');

        /** @var \Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface $warmerService */
        $warmerService = $this->objectManager->create('Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface');

        foreach ($pageRepository->getCollection() as $page) {
            $status = $pageService->isCached($page) ? 'Cached' : 'Not cached';

            $collection = $pageRepository->getCollection()
                ->addFieldToFilter(PageInterface::ID, $page->getId());

            foreach ($warmerService->warmCollection($collection, null) as $warmStatus) {
                $output->writeln($warmStatus->toString());
            }

            if ($pageService->isCached($page)) {
                $output->writeln("<comment>$status</comment> <info>WARMED</info> {$page->getUri()}");
            } else {
                $output->writeln("<comment>$status</comment> <error>NOT WARMED</error> {$page->getUri()}");
            }
        }
    }
}
