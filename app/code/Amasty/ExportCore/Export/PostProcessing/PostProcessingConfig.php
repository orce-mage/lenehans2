<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\PostProcessing;

use Amasty\ExportCore\Api\PostProcessing\PostProcessingConfigInterface;

class PostProcessingConfig implements PostProcessingConfigInterface
{
    /**
     * @var array
     */
    private $postProcessingConfig = [];

    public function __construct(array $postProcessingConfig)
    {
        foreach ($postProcessingConfig as $config) {
            if (!isset($config['code'], $config['processorClass'])) {
                throw new \LogicException('Export processor "' . $config['code'] . ' is not configured properly');
            }
            $this->postProcessingConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->postProcessingConfig[$type])) {
            throw new \RuntimeException('Processor "' . $type . '" is not defined');
        }

        return $this->postProcessingConfig[$type];
    }

    public function all(): array
    {
        return $this->postProcessingConfig;
    }
}
