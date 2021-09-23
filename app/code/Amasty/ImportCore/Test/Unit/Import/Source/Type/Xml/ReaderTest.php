<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\Source\Type\Xml;

use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Amasty\ImportCore\Import\Source\Type\Xml\Reader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->reader = $objectManager->getObject(Reader::class);
    }

    public function testParseSubEntities()
    {
        $entity = new \SimpleXMLElement(file_get_contents(__DIR__ . '/../../../../_files/test_entity.xml'));

        $dataStructure = $this->createConfiguredMock(
            SourceDataStructure::class,
            [
                'getFields' => ['entity_id', 'state'],
                'getMap' => 'order',
                'getSubEntityStructures' => [
                    $this->createConfiguredMock(
                        SourceDataStructure::class,
                        [
                            'getFields' => ['entity_id', 'order_id'],
                            'getMap' => 'sales_invoice',
                            'getSubEntityStructures' => []
                        ]
                    ),
                    $this->createConfiguredMock(
                        SourceDataStructure::class,
                        [
                            'getFields' => ['item_id', 'order_id'],
                            'getMap' => 'sales_order_item',
                            'getSubEntityStructures' => []
                        ]
                    ),
                    $this->createConfiguredMock(
                        SourceDataStructure::class,
                        [
                            'getFields' => ['entity_id', 'order_id'],
                            'getMap' => 'sales_shipment',
                            'getSubEntityStructures' => [
                                $this->createConfiguredMock(
                                    SourceDataStructure::class,
                                    [
                                        'getFields' => ['entity_id', 'parent_id'],
                                        'getMap' => 'sales_shipment_item',
                                        'getSubEntityStructures' => []
                                    ]
                                )
                            ]
                        ]
                    )
                ]
            ]
        );
        $expected = [
            'entity_id' => '1',
            'state' => 'test',
            'sales_invoice' => [],
            'sales_order_item' => [
                [
                    'item_id' => '1',
                    'order_id' => '1'
                ],
                [
                    'item_id' => '2',
                    'order_id' => '1'
                ],
            ],
            'sales_shipment' => [
                [
                    'entity_id' => '1',
                    'order_id' => '1',
                    'sales_shipment_item' => [
                        [
                            'entity_id' => '1',
                            'parent_id' => '1',
                        ],
                        [
                            'entity_id' => '2',
                            'parent_id' => '1',
                        ],
                    ],
                ],
                [
                    'entity_id' => '2',
                    'order_id' => '1',
                    'sales_shipment_item' => [
                        0 => [
                            'entity_id' => '3',
                            'parent_id' => '2',
                        ],
                    ],
                ],
                [
                    'entity_id' => '3',
                    'order_id' => '1',
                    'sales_shipment_item' => [
                        [
                            'entity_id' => '4',
                            'parent_id' => '3',
                        ],
                        [
                            'entity_id' => '5',
                            'parent_id' => '3',
                        ],
                    ],
                ],
            ],
        ];
        $readerReflection = new \ReflectionClass(Reader::class);

        $pathPartsProp = $readerReflection->getProperty('pathParts');
        $pathPartsProp->setAccessible(true);
        $pathPartsProp->setValue($this->reader, ['item']);

        $method = $readerReflection->getMethod('parseSubEntities');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->reader, [(array)$entity->item, $dataStructure]);

        $this->assertEquals($expected, $result);
    }
}
