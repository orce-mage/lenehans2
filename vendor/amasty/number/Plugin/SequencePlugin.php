<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Plugin;

use Amasty\Number\Model\ConfigProvider;
use Amasty\Number\Model\Counter\Scope\CounterScopeResolver;
use Amasty\Number\Model\Number\Generator;
use Amasty\Number\Model\SequenceStorage;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Psr\Log\LoggerInterface;

class SequencePlugin
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SequenceStorage
     */
    private $sequenceStorage;

    /**
     * @var CounterScopeResolver
     */
    private $counterScopeResolver;

    public function __construct(
        Generator $generator,
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        SequenceStorage $sequenceStorage,
        CounterScopeResolver $counterScopeResolver
    ) {
        $this->generator = $generator;
        $this->configProvider = $configProvider;
        $this->logger = $logger;
        $this->sequenceStorage = $sequenceStorage;
        $this->counterScopeResolver = $counterScopeResolver;
    }

    /**
     * After get order's increment ID
     *
     * @param SequenceInterface $subject
     * @param $incrementId
     * @return string
     */
    public function afterGetNextValue(
        SequenceInterface $subject,
        $incrementId
    ) {
        if ($this->configProvider->isEnabled()
            && $this->sequenceStorage->getEntityType() == ConfigProvider::ORDER_TYPE
        ) {
            try {
                $incrementId = $this->generator->getNextFormattedNumber(ConfigProvider::ORDER_TYPE);
            } catch (\Throwable $e) {
                $this->logger->critical($e);
            }
        }

        return $incrementId;
    }
}
