<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver;

use Amasty\ImportCore\Api\FileResolver\FileResolverConfigInterface;
use Amasty\ImportCore\Api\FileResolver\FileResolverInterface;
use Magento\Framework\ObjectManagerInterface;

class FileResolverAdapter
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FileResolverConfigInterface
     */
    private $fileResolverConfig;

    public function __construct(
        ObjectManagerInterface $objectManager,
        FileResolverConfigInterface $fileResolverConfig
    ) {
        $this->objectManager = $objectManager;
        $this->fileResolverConfig = $fileResolverConfig;
    }

    public function getFileResolver(string $type): FileResolverInterface
    {
        $fileResolverClass = $this->fileResolverConfig->get($type)['fileResolverClass'];

        if (!is_subclass_of($fileResolverClass, FileResolverInterface::class)) {
            throw new \RuntimeException('Wrong file resolver class: "' . $fileResolverClass . "'");
        }

        return $this->objectManager->create($fileResolverClass);
    }
}
