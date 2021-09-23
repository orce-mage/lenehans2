<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Toggle;

class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $value;

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): ConfigInterface
    {
        $this->value = $value;

        return $this;
    }
}
