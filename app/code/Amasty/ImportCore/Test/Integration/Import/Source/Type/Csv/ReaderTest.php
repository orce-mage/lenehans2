<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\Import\Source\Type\Csv;

use Amasty\ImportCore\Import\Source\Type\Csv\Config;
use Amasty\ImportCore\Import\Source\Type\Csv\Reader;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->reader = $objectManager->get(
            Reader::class
        );
    }

    /**
     * @dataProvider readRowDataProvider
     */
    public function testReadRow($isMerged, $rows, $structure, $expected)
    {
        $fileReader = $this->createPartialMock(
            Filesystem\File\Read::class,
            ['readCsv']
        );
        $config = $this->createConfiguredMock(
            Config::class,
            [
                'isCombineChildRows' => $isMerged,
                'getChildRowSeparator' => Config::SETTING_CHILD_ROW_SEPARATOR,
                'getMaxLineLength' => Config::SETTING_MAX_LINE_LENGTH,
                'getSeparator' => Config::SETTING_FIELD_DELIMITER,
                'getEnclosure' => Config::SETTING_FIELD_ENCLOSURE_CHARACTER
            ]
        );

        $readerReflection = new \ReflectionClass(Reader::class);

        $configProp = $readerReflection->getProperty('config');
        $configProp->setAccessible(true);
        $configProp->setValue($this->reader, $config);

        $fileReaderProp = $readerReflection->getProperty('fileReader');
        $fileReaderProp->setAccessible(true);
        $fileReaderProp->setValue($this->reader, $fileReader);

        // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed
        for ($i = 0; $i < count($rows); $i++) {
            $fileReader->expects($this->at($i))->method('readCsv')->willReturn($rows[$i]);
        }
        $this->setHeaderStructure($structure);
        $result = $this->reader->readRow();
        $this->assertEquals($expected, $result);
    }

    private function setHeaderStructure($structure)
    {
        $reflection = new \ReflectionClass(Reader::class);
        $property = $reflection->getProperty('headerStructure');
        $property->setAccessible(true);
        $property->setValue($this->reader, $structure);
    }

    public function readRowDataProvider()
    {
        $structure1 = [
            'entity_id' => '1',
            'status' => '',
            'o_item' => [
                'item_id' => '1',
                'order_id' => '2',
                'name' => ''
            ]
        ];
        $expected1 = [
            'entity_id' => '1',
            'status' => 'test',
            'o_item' => [
                [
                    'item_id' => '1',
                    'order_id' => '1',
                    'name' => 'test'
                ],
                [
                    'item_id' => '2',
                    'order_id' => '1',
                    'name' => 'test2'
                ],

            ]
        ];
        $structure2 = [
            'entity_id' => '1',
            'status' => '',
            'shipment' => [
                'entity_id' => '1',
                'order_id' => '2',
                'total_qty' => '',
                'shipment_item' => [
                    'entity_id' => '1',
                    'shipment_id' => '2',
                    'name' => ''
                ]
            ]
        ];
        $expected2 = [
            'entity_id' => '1',
            'status' => 'test',
            'shipment' => [
                [
                    'entity_id' => '1',
                    'order_id' => '1',
                    'total_qty' => '2',
                    'shipment_item' => [
                        [
                            'entity_id' => '1',
                            'shipment_id' => '1',
                            'name' => 'test1'
                        ],
                        [
                            'entity_id' => '2',
                            'shipment_id' => '1',
                            'name' => 'test2'
                        ],
                    ]
                ],
                [
                    'entity_id' => '2',
                    'order_id' => '1',
                    'total_qty' => '1',
                    'shipment_item' => [
                        [
                            'entity_id' => '3',
                            'shipment_id' => '2',
                            'name' => 'test1'
                        ]
                    ]
                ],
                [
                    'entity_id' => '3',
                    'order_id' => '1',
                    'total_qty' => '2',
                    'shipment_item' => [
                        [
                            'entity_id' => '4',
                            'shipment_id' => '3',
                            'name' => 'test1'
                        ],
                        [
                            'entity_id' => '5',
                            'shipment_id' => '3',
                            'name' => 'test2'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [ //case 1: one sub entity, 2 rows, not merged
                false,
                [
                    ['1', 'test', '1', '1', 'test'],
                    ['', '', '2', '1', 'test2']

                ],
                $structure1,
                $expected1
            ],
            [ //case 2: one sub entity, merged
                true,
                [
                    ['1', 'test', '1,2', '1,1', 'test,test2']
                ],
                $structure1,
                $expected1
            ],
            [ //case 3: two sub entity (first shipment - two items, second - one, third - two, not merged
                false,
                [
                    ['1', 'test', '1', '1', '2', '1', '1', 'test1'],
                    ['', '', '', '', '', '2', '1', 'test2'],
                    ['', '', '2', '1', '1', '3', '2', 'test1'],
                    ['', '', '3', '1', '2', '4', '3', 'test1'],
                    ['', '', '', '', '', '5', '3', 'test2']
                ],
                $structure2,
                $expected2
            ],
            [ //case 4: same case as third but merged
                true,
                [
                    ['1', 'test', '1,2,3', '1,1,1', '2,1,2', '1,2,3,4,5', '1,1,2,3,3', 'test1,test2,test1,test1,test2']
                ],
                $structure2,
                $expected2
            ]
        ];
    }
}
