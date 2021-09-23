<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Template;

use Amasty\ExportCore\Api\Template\RendererInterface;
use Magento\Framework\ObjectManagerInterface;

class RendererProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TemplateConfig
     */
    private $templateConfig;

    public function __construct(
        ObjectManagerInterface $objectManager,
        TemplateConfig $templateConfig
    ) {
        $this->objectManager = $objectManager;
        $this->templateConfig = $templateConfig;
    }

    public function getRenderer(string $type): RendererInterface
    {
        $rendererClass = $this->templateConfig->get($type)['rendererClass'];

        if (!is_subclass_of($rendererClass, RendererInterface::class)) {
            throw new \RuntimeException('Wrong source renderer class: "' . $rendererClass);
        }

        return $this->objectManager->create($rendererClass);
    }
}
