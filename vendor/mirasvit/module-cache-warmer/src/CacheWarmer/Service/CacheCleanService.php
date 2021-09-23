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



namespace Mirasvit\CacheWarmer\Service;

use Mirasvit\CacheWarmer\Api\Data\TraceInterface;
use Mirasvit\CacheWarmer\Api\Repository\TraceRepositoryInterface;
use Mirasvit\CacheWarmer\Logger\FlushLogger;
use Mirasvit\CacheWarmer\Model\Config;

class CacheCleanService
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FlushLogger
     */
    private $logger;

    /**
     * @var TraceRepositoryInterface
     */
    private $traceRepository;

    /**
     * CacheCleanService constructor.
     * @param Config $config
     * @param FlushLogger $logger
     * @param TraceRepositoryInterface $traceRepository
     */
    public function __construct(
        Config $config,
        FlushLogger $logger,
        TraceRepositoryInterface $traceRepository
    ) {
        $this->config          = $config;
        $this->logger          = $logger;
        $this->traceRepository = $traceRepository;
    }

    /**
     * @param string $mode
     * @param array  $tags
     */
    public function logCacheClean($mode, array $tags)
    {
        $allowed = [
            "rma_order_status_history",
            "helpdesk_gateway",
            "rewards_transaction",
            "helpdesk_message",
            "helpdesk_ticket",
        ];
        if (count(array_intersect($allowed, $tags)) != 0) {
            return;
        }

        $isTagLogEnabled       = $this->config->isTagLogEnabled();
        $isBacktraceLogEnabled = $this->config->isBacktraceLogEnabled();

        if ($isTagLogEnabled) {
            $this->logger->debug('Clean cache', [
                'mode'      => $mode,
                'tags'      => $tags,
                'backtrace' => $isBacktraceLogEnabled ? \Magento\Framework\Debug::backtrace(true, false, false) : null,
            ]);
        }

        $url = $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

        $traceData = [
            'cli'       => php_sapi_name() == "cli" ? "Yes" : "No",
            'url'       => $url ? $url : "N/A",
            'mode'      => $mode,
            'tags'      => $tags,
            'backtrace' => \Magento\Framework\Debug::backtrace(true, false, false),
        ];

        $trace = $this->traceRepository->create();
        $trace->setEntityType(TraceInterface::ENTITY_TYPE_CACHE)
            ->setEntityId(0)
            ->setTrace($traceData)
            ->setStartedAt(date('Y-m-d H:i:s'))
            ->setFinishedAt(date('Y-m-d H:i:s'));
        try {
            $this->traceRepository->save($trace);
        } catch (\Exception $e) {
            // migration can be not completed yet
        }
    }
}
