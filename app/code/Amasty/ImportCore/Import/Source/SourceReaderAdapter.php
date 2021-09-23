<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source;

use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Amasty\ImportCore\Api\Source\SourceReaderInterface;
use Magento\Framework\ObjectManagerInterface;

class SourceReaderAdapter
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    public function __construct(
        ObjectManagerInterface $objectManager,
        SourceConfigInterface $sourceConfig
    ) {
        $this->objectManager = $objectManager;
        $this->sourceConfig = $sourceConfig;
    }

    /**
     * @param string $type
     *
     * @return SourceReaderInterface
     */
    public function getReader(string $type): SourceReaderInterface
    {
        $readerClass = $this->sourceConfig->get($type)['readerClass'];
        if (!is_subclass_of($readerClass, SourceReaderInterface::class)) {
            throw new \RuntimeException('Wrong source reader class: "' . $readerClass);
        }

        return $this->objectManager->create($readerClass);
    }
}
