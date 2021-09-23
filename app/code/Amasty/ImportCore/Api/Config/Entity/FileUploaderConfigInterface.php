<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Entity;

interface FileUploaderConfigInterface
{
    public function getFileUploader();

    public function getFileUploaderClass();
    public function setFileUploaderClass(string $class): void;

    public function setStoragePath(string $storagePath): void;
    public function getStoragePath(): string;
}
