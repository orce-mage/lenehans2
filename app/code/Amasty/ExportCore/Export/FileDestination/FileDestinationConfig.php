<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\FileDestination;

use Amasty\ExportCore\Api\FileDestination\FileDestinationConfigInterface;

class FileDestinationConfig implements FileDestinationConfigInterface
{
    /**
     * @var array
     */
    private $fileDestinationConfig = [];

    public function __construct(array $fileDestinationConfig)
    {
        foreach ($fileDestinationConfig as $config) {
            if (!isset($config['code'], $config['fileDestinationClass'])) {
                throw new \LogicException('File Destination "' . $config['code'] . ' is not configured properly');
            }
            $this->fileDestinationConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->fileDestinationConfig[$type])) {
            throw new \RuntimeException('File Destination "' . $type . '" is not defined');
        }

        return $this->fileDestinationConfig[$type];
    }

    public function all(): array
    {
        return $this->fileDestinationConfig;
    }
}
