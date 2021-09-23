<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\SchemaReader\Import\Config;

use Amasty\ImportCore\SchemaReader\Config\Converter;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var string
     */
    protected $schemaContent;

    public function setUp(): void
    {
        $urnResolver = new UrnResolver();
        $importXsd = $urnResolver->getRealPath('urn:amasty:module:Amasty_ImportCore:etc/am_import.xsd');
        $argumentsSchema = 'urn:magento:framework:Data/etc/argument/types.xsd';
        $argumentsXsd = $urnResolver->getRealPath($argumentsSchema);
        $this->schemaContent = str_replace($argumentsSchema, $argumentsXsd, \file_get_contents($importXsd));
    }

    /**
     * @dataProvider convertDataProvider
     * @param string $inputData
     * @param array $expectedResult
     */
    public function testConvert(string $inputData, array $expectedResult)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Converter $converter */
        $converter = $objectManager->get(Converter::class);

        $dom = new \DOMDocument();
        $dom->loadXML($inputData);
        $dom->schemaValidateSource($this->schemaContent, LIBXML_SCHEMA_CREATE);
        $result = $converter->convert($dom);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for convert test
     * @return array
     */
    public function convertDataProvider(): array
    {
        return [
            'basic' => [
                \file_get_contents(__DIR__ . '/../../../_files/converter_files/basic.xml'),
                json_decode(\file_get_contents(__DIR__ . '/../../../_files/converter_files/basic.json'), true)
            ],
            'behavior' => [
                \file_get_contents(__DIR__ . '/../../../_files/converter_files/behaviors.xml'),
                json_decode(\file_get_contents(__DIR__ . '/../../../_files/converter_files/behaviors.json'), true)
            ],
            'fields' => [
                \file_get_contents(__DIR__ . '/../../../_files/converter_files/fields.xml'),
                json_decode(\file_get_contents(__DIR__ . '/../../../_files/converter_files/fields.json'), true)
            ],
            'sample_data' => [
                \file_get_contents(__DIR__ . '/../../../_files/converter_files/sample_data.xml'),
                json_decode(\file_get_contents(__DIR__ . '/../../../_files/converter_files/sample_data.json'), true)
            ]
        ];
    }
}
