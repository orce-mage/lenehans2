<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Conclusion;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FileDestination\FileDestinationInterface;
use Amasty\ExportCore\Export\FileDestination\FileDestinationProvider;
use Psr\Log\LoggerInterface;

class DeliveryAction implements ActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileDestinationProvider
     */
    private $fileDestinationProvider;

    /**
     * @var FileDestinationInterface[]
     */
    private $destinations = [];

    public function __construct(
        FileDestinationProvider $fileDestinationProvider,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->fileDestinationProvider = $fileDestinationProvider;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        foreach ($this->destinations as $code => $destination) {
            try {
                $destination->execute($exportProcess);
            } catch (\Exception $e) {
                $this->logger->error($e);
                $exportProcess->addWarningMessage(__(
                    'Failed to deploy to destination "%1". Error "%2". Please check your log file for more details',
                    $code,
                    $e->getMessage()
                ));
            }
        }
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        if ($exportProcess->getProfileConfig()->getFileDestinationTypes()) {
            foreach ($exportProcess->getProfileConfig()->getFileDestinationTypes() as $fileDestinationType) {
                $this->destinations[$fileDestinationType] = $this->fileDestinationProvider->getDestination(
                    $fileDestinationType
                );
            }
        }
    }
}
