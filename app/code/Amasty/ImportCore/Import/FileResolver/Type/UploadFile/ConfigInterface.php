<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\UploadFile;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $hash
     *
     * @return void
     */
    public function setHash(string $hash): void;
}
