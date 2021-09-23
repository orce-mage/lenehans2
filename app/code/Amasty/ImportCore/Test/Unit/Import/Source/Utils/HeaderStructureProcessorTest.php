<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\Source\Utils;

use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Amasty\ImportCore\Import\Source\Utils\HeaderStructureProcessor;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class HeaderStructureProcessorTest extends TestCase
{
    /**
     * @dataProvider getHeaderStructureDataProvider
     */
    public function testGetHeaderStructure($headerRow, $rowNumbersToSkip)
    {
        $objectManager = new ObjectManager($this);
        $arrayManager = $objectManager->getObject(ArrayManager::class);
        $processor = $objectManager->getObject(
            HeaderStructureProcessor::class,
            ['arrayManager' => $arrayManager]
        );

        $expectedStructure = [
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
        $dataStructure = $this->createConfiguredMock(
            SourceDataStructure::class,
            [
                'getIdFieldName' => 'entity_id',
                'getFields' => ['entity_id', 'status'],
                'getMap' => 'order',
                'getSubEntityStructures' => [
                    $this->createConfiguredMock(
                        SourceDataStructure::class,
                        [
                            'getIdFieldName' => 'entity_id',
                            'getParentIdFieldName' => 'order_id',
                            'getFields' => ['entity_id', 'order_id', 'total_qty'],
                            'getMap' => 'shipment',
                            'getSubEntityStructures' => [
                                $this->createConfiguredMock(
                                    SourceDataStructure::class,
                                    [
                                        'getIdFieldName' => 'entity_id',
                                        'getParentIdFieldName' => 'shipment_id',
                                        'getFields' => ['entity_id', 'shipment_id', 'name'],
                                        'getMap' => 'shipment_item',
                                        'getSubEntityStructures' => []
                                    ]
                                )
                            ]
                        ]
                    )
                ]
            ]
        );
        $result = $processor->getHeaderStructure($dataStructure, $headerRow, '.');
        $this->assertEquals(
            $expectedStructure,
            $result
        );
        $this->assertEquals($rowNumbersToSkip, $processor->getColNumbersToSkip());
    }

    public function getHeaderStructureDataProvider()
    {
        return [
            [// case 1: header row and dataStructure have same amount of columns
                [
                    'order.status',
                    'order.entity_id',
                    'shipment.entity_id',
                    'shipment.order_id',
                    'shipment.total_qty',
                    'shipment_item.entity_id',
                    'shipment_item.shipment_id',
                    'shipment_item.name'
                ],
                []
            ],
            [// case 2: header row have more rows then data structure
                [
                    'order.status',
                    'order.entity_id',
                    'order.test',
                    'shipment.entity_id',
                    'shipment.order_id',
                    'shipment.total_qty',
                    'shipment.test',
                    'shipment_item.entity_id',
                    'shipment_item.shipment_id',
                    'shipment_item.name'
                ],
                [2, 6]
            ],
            [// case 3: header row have more rows then data structure and they ordered differently
                [
                    'order.test',
                    'order.entity_id',
                    'order.status',
                    'shipment.entity_id',
                    'shipment.total_qty',
                    'shipment.test',
                    'shipment.order_id',
                    'shipment_item.entity_id',
                    'shipment_item.name',
                    'shipment_item.shipment_id'
                ],
                [0, 5]
            ]
        ];
    }
}
