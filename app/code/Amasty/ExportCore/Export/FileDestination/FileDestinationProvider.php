<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\FileDestination;

use Amasty\ExportCore\Api\FileDestination\FileDestinationConfigInterface;
use Amasty\ExportCore\Api\FileDestination\FileDestinationInterface;
use Magento\Framework\ObjectManagerInterface;

class FileDestinationProvider
{
    /**
     * @var FileDestinationConfigInterface
     */
    private $destinationConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        FileDestinationConfigInterface $destinationConfig
    ) {
        $this->objectManager = $objectManager;
        $this->destinationConfig = $destinationConfig;
    }

    public function getDestination(string $type): FileDestinationInterface
    {
        $destinationClass = $this->destinationConfig->get($type)['fileDestinationClass'];

        if (!is_subclass_of($destinationClass, FileDestinationInterface::class)) {
            throw new \RuntimeException('Wrong file destination class: "' . $destinationClass);
        }

        return $this->objectManager->create($destinationClass);
    }
}
