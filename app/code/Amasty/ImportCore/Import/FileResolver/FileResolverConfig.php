<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\FileResolver;

use Amasty\ImportCore\Api\FileResolver\FileResolverConfigInterface;

class FileResolverConfig implements FileResolverConfigInterface
{
    /**
     * @var array
     */
    private $fileResolverConfig = [];

    public function __construct(array $fileResolverConfig)
    {
        foreach ($fileResolverConfig as $config) {
            if (!isset($config['code'], $config['fileResolverClass'])) {
                throw new \LogicException('File Resolver "' . $config['code'] . ' is not configured properly');
            }
            $this->fileResolverConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->fileResolverConfig[$type])) {
            throw new \RuntimeException('File Resolver "' . $type . '" is not defined');
        }

        return $this->fileResolverConfig[$type];
    }

    public function all(): array
    {
        return $this->fileResolverConfig;
    }
}
