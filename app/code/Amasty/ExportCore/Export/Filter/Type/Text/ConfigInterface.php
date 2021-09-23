<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Text;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Text\ConfigInterface
     */
    public function setValue(?string $value): ConfigInterface;
}
