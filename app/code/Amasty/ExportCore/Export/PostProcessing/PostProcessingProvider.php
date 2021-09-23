<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\PostProcessing;

use Amasty\ExportCore\Api\PostProcessing\ProcessorInterface;
use Magento\Framework\ObjectManagerInterface;

class PostProcessingProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PostProcessingConfig
     */
    private $postProcessingConfig;

    public function __construct(
        ObjectManagerInterface $objectManager,
        PostProcessingConfig $templateConfig
    ) {
        $this->objectManager = $objectManager;
        $this->postProcessingConfig = $templateConfig;
    }

    public function getProcessor(string $type): ProcessorInterface
    {
        $processorClass = $this->postProcessingConfig->get($type)['processorClass'];

        if (!is_subclass_of($processorClass, ProcessorInterface::class)) {
            throw new \RuntimeException('Wrong processor class: "' . $processorClass);
        }

        return $this->objectManager->create($processorClass);
    }
}
