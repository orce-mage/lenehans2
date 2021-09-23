<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Cleanup;

use Amasty\ImportCore\Api\Action\CleanerInterface;
use Magento\Framework\ObjectManagerInterface;

class CleanerProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $cleaners = [
        'batch' => ['class' => BatchCleaner::class]
    ];

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $cleaners = []
    ) {
        $this->objectManager = $objectManager;
        $this->cleaners = array_merge($this->cleaners, $cleaners);
    }

    /**
     * Get cleaner instances for specified entity code
     *
     * @param string $entityCode
     * @return CleanerInterface[]
     */
    public function getCleaners(string $entityCode): array
    {
        $cleanerInstances = [];
        foreach ($this->cleaners as $cleaner) {
            if (isset($cleaner['entities'])
                && is_array($cleaner['entities'])
                && !in_array($entityCode, $cleaner['entities'])
            ) {
                continue;
            }
            if (!isset($cleaner['class'])) {
                throw new \RuntimeException('Cleaner classname isn\'t specified.');
            }

            $instance = $this->objectManager->create($cleaner['class']);
            if (!$instance instanceof CleanerInterface) {
                throw new \RuntimeException(
                    'Cleaner class ' . $cleaner['class'] . ' doesn\'t implement '
                    . CleanerInterface::class
                );
            }

            $cleanerInstances[] = $instance;
        }

        return $cleanerInstances;
    }
}
