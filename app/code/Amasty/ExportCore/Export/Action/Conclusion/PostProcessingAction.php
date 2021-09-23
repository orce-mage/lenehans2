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
use Amasty\ExportCore\Api\PostProcessing\ProcessorInterface;
use Amasty\ExportCore\Export\PostProcessing\PostProcessingProvider;

class PostProcessingAction implements ActionInterface
{
    /**
     * @var PostProcessingProvider
     */
    private $postProcessingProvider;

    /**
     * @var ProcessorInterface[]
     */
    private $processors = [];

    public function __construct(
        PostProcessingProvider $postProcessingProvider
    ) {
        $this->postProcessingProvider = $postProcessingProvider;
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        foreach ($exportProcess->getProfileConfig()->getPostProcessors() ?? [] as $type) {
            $this->processors[$type] = $this->postProcessingProvider->getProcessor($type);
        }
    }

    public function execute(ExportProcessInterface $exportProcess)
    {
        foreach ($this->processors as $processor) {
            $processor->process($exportProcess);
        }
    }
}
