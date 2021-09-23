<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Template\Type\Csv;

use Magento\Framework\DataObject;

class Config extends DataObject implements ConfigInterface
{
    const SETTING_MAX_LINE_LENGTH = 0;
    const SETTING_FIELD_DELIMITER = ',';
    const SETTING_FIELD_ENCLOSURE_CHARACTER = '"';
    const SETTING_HAS_HEADER_ROW = true;
    const SETTING_COMBINE_CHILD_ROWS = false;
    const SETTING_CHILD_ROW_SEPARATOR = ',';
    const SETTING_POSTFIX = '.';

    const SEPARATOR = 'separator';
    const ENCLOSURE = 'enclosure';
    const HAS_HEADER_ROW = 'has_header_row';
    const COMBINE_CHILD_ROWS = 'combine_child_rows';
    const CHILD_ROW_SEPARATOR = 'child_row_separator';
    const MAX_LINE_LENGTH = 'max_line_length';
    const POSTFIX = 'postfix';

    public function getSeparator(): ?string
    {
        return $this->getData(self::SEPARATOR) ?? self::SETTING_FIELD_DELIMITER;
    }

    public function setSeparator(?string $separator): ConfigInterface
    {
        $this->setData(self::SEPARATOR, $separator);

        return $this;
    }

    public function getEnclosure(): ?string
    {
        return $this->getData(self::ENCLOSURE) ?? self::SETTING_FIELD_ENCLOSURE_CHARACTER;
    }

    public function setEnclosure(?string $enclosure): ConfigInterface
    {
        $this->setData(self::ENCLOSURE, $enclosure);

        return $this;
    }

    public function isHasHeaderRow(): ?bool
    {
        return $this->getData(self::HAS_HEADER_ROW) ?? self::SETTING_HAS_HEADER_ROW;
    }

    public function setHasHeaderRow(?bool $hasHeaderRow): ConfigInterface
    {
        $this->setData(self::HAS_HEADER_ROW, $hasHeaderRow);

        return $this;
    }

    public function getMaxLineLength(): ?int
    {
        return $this->getData(self::MAX_LINE_LENGTH) ?? self::SETTING_MAX_LINE_LENGTH;
    }

    public function setMaxLineLength(?int $maxLineLength): ConfigInterface
    {
        $this->setData(self::MAX_LINE_LENGTH, $maxLineLength);

        return $this;
    }

    public function getPostfix(): ?string
    {
        return $this->getData(self::POSTFIX) ?? self::SETTING_POSTFIX;
    }

    public function setPostfix(?string $postfix): ConfigInterface
    {
        $this->setData(self::POSTFIX, $postfix);

        return $this;
    }

    public function isCombineChildRows(): ?bool
    {
        return $this->getData(self::COMBINE_CHILD_ROWS) ?? self::SETTING_COMBINE_CHILD_ROWS;
    }

    public function setCombineChildRows(?bool $combineChildRows): ConfigInterface
    {
        $this->setData(self::COMBINE_CHILD_ROWS, $combineChildRows);

        return $this;
    }

    public function getChildRowSeparator(): ?string
    {
        return $this->getData(self::CHILD_ROW_SEPARATOR) ?? self::SETTING_CHILD_ROW_SEPARATOR;
    }

    public function setChildRowSeparator(?string $childRowSeparator): ConfigInterface
    {
        $this->setData(self::CHILD_ROW_SEPARATOR, $childRowSeparator);

        return $this;
    }
}
