<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Filter\Type\Select;

interface ConfigInterface
{
    /**
     * @return string[]|null
     */
    public function getValue(): ?array;

    /**
     * @param string[] $value
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Select\ConfigInterface
     */
    public function setValue(?array $value): ConfigInterface;

    /**
     * @return bool|null
     */
    public function getIsMultiselect(): ?bool;

    /**
     * @param bool $isMultiselect
     *
     * @return \Amasty\ExportCore\Export\Filter\Type\Select\ConfigInterface
     */
    public function setIsMultiselect($isMultiselect): ConfigInterface;
}
