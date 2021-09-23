<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Template\Type\Csv;

interface ConfigInterface
{
    /**
     * @return string|null
     */
    public function getSeparator(): ?string;

    /**
     * @param string|null $separator
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setSeparator(?string $separator): ConfigInterface;

    /**
     * @return string|null
     */
    public function getEnclosure(): ?string;

    /**
     * @param string|null $enclosure
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setEnclosure(?string $enclosure): ConfigInterface;

    /**
     * @return bool|null
     */
    public function isHasHeaderRow(): ?bool;

    /**
     * @param bool|null $hasHeaderRow
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setHasHeaderRow(?bool $hasHeaderRow): ConfigInterface;

    /**
     * @return bool|null
     */
    public function isCombineChildRows(): ?bool;

    /**
     * @param bool|null $combineChildRows
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setCombineChildRows(?bool $combineChildRows): ConfigInterface;

    /**
     * @return string|null
     */
    public function getChildRowSeparator(): ?string;

    /**
     * @param string|null $childRowSeparator
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setChildRowSeparator(?string $childRowSeparator): ConfigInterface;

    /**
     * @return int|null
     */
    public function getMaxLineLength(): ?int;

    /**
     * @param int|null $maxLineLength
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setMaxLineLength(?int $maxLineLength): ConfigInterface;

    /**
     * @return string|null
     */
    public function getPostfix(): ?string;

    /**
     * @param string|null $postfix
     *
     * @return \Amasty\ExportCore\Export\Template\Type\Csv\ConfigInterface
     */
    public function setPostfix(?string $postfix): ConfigInterface;
}
