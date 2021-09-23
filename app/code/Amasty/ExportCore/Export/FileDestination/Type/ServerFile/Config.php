<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\FileDestination\Type\ServerFile;

class Config implements ConfigInterface
{
    private $filename;

    private $filepath;

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(?string $filepath): ConfigInterface
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): ConfigInterface
    {
        $this->filename = $filename;

        return $this;
    }
}
