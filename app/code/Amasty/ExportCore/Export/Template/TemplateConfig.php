<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Template;

use Amasty\ExportCore\Api\Template\TemplateConfigInterface;

class TemplateConfig implements TemplateConfigInterface
{
    /**
     * @var array
     */
    private $templateConfig = [];

    public function __construct(array $templateConfig)
    {
        foreach ($templateConfig as $config) {
            if (!isset($config['code'], $config['rendererClass'])) {
                throw new \LogicException('Export template "' . $config['code'] . ' is not configured properly');
            }
            $this->templateConfig[$config['code']] = $config;
        }
    }

    public function get(string $type): array
    {
        if (!isset($this->templateConfig[$type])) {
            throw new \RuntimeException('Template "' . $type . '" is not defined');
        }

        return $this->templateConfig[$type];
    }

    public function all(): array
    {
        return $this->templateConfig;
    }
}
