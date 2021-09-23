<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\ServerFile;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param $filename
     *
     * @return void
     */
    public function setFilename(string $filename): void;
}
