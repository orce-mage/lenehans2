<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source\Type\Csv;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getSeparator(): ?string;

    /**
     * @param string|null $separator
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setSeparator(?string $separator): ConfigInterface;

    /**
     * @return string|null
     */
    public function getEnclosure(): ?string;

    /**
     * @param string|null $enclosure
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setEnclosure(?string $enclosure): ConfigInterface;

    /**
     * @return bool|null
     */
    public function isCombineChildRows(): ?bool;

    /**
     * @param bool|null $combineChildRows
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setCombineChildRows(?bool $combineChildRows): ConfigInterface;

    /**
     * @return string|null
     */
    public function getChildRowSeparator(): ?string;

    /**
     * @param string|null $childRowSeparator
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setChildRowSeparator(?string $childRowSeparator): ConfigInterface;

    /**
     * @return int|null
     */
    public function getMaxLineLength(): ?int;

    /**
     * @param int|null $maxLineLength
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setMaxLineLength(?int $maxLineLength): ConfigInterface;

    /**
     * @return string|null
     */
    public function getPrefix(): ?string;

    /**
     * @param string|null $prefix
     *
     * @return \Amasty\ImportCore\Import\Source\Type\Csv\ConfigInterface
     */
    public function setPrefix(?string $prefix): ConfigInterface;
}
