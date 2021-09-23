<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Type\Xml;

use Amasty\ImportCore\Api\Source\SourceGeneratorInterface;

class Generator implements SourceGeneratorInterface
{
    const TAB_SIZE = 2;

    public function generate(array $data): string
    {
        $xmlContent = "<?xml version=\"1.0\"?>\n<items>\n" . $this->renderLevel($data) . "</items>";
        $xml = new \SimpleXMLElement($xmlContent);

        return $xml->saveXML();
    }

    public function getExtension(): string
    {
        return 'xml';
    }

    protected function renderLevel(
        array $data,
        int $level = 0
    ): string {
        $contentIndent = str_repeat(' ', ($level * 2 + 1) * self::TAB_SIZE);
        $rowIndent = str_repeat(' ', ($level * 2 + 2) * self::TAB_SIZE);
        $result = [];

        foreach ($data as $row) {
            $rowData = '';
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $nodeValue = $this->renderLevel(
                        $value,
                        $level + 1
                    );
                    $rowData .=  "{$rowIndent}<{$key}>\n{$nodeValue}{$rowIndent}</{$key}>\n";
                } else {
                    //phpcs:ignore
                    $value = is_string($value) ? htmlspecialchars($value, ENT_XML1) : $value;
                    $rowData .= "{$rowIndent}<{$key}>{$value}</{$key}>\n";
                }
            }
            $result[] = "{$contentIndent}<item>\n{$rowData}{$contentIndent}</item>\n";
        }

        return implode($result);
    }
}
