<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Action\DataPrepare\Source;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Action\DataPrepare\Source\SourceDataProcessor;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Action\DataPrepare\Source\SourceDataProcessor
 */
class SourceDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SourceDataProcessor
     */
    private $sourceDataProcessor;

    /**
     * @var DataStructureProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataStructureProviderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->dataStructureProviderMock = $this->createMock(DataStructureProvider::class);
        $this->sourceDataProcessor = $objectManager->getObject(
            SourceDataProcessor::class,
            ['dataStructureProvider' => $this->dataStructureProviderMock]
        );
    }

    /**
     * @param array $row
     * @param SourceDataStructureInterface|\PHPUnit_Framework_MockObject_MockObject $dataStructureMock
     * @param array $expectedResult
     * @dataProvider convertToImportProcessStructureDataProvider
     */
    public function testConvertToImportProcessStructure(
        $row,
        $dataStructureMock,
        $expectedResult
    ) {
        /** @var ImportProcessInterface|\PHPUnit_Framework_MockObject_MockObject $importProcessMock */
        $importProcessMock = $this->createMock(ImportProcessInterface::class);
        $entityConfigMock = $this->createMock(EntityConfigInterface::class);
        $profileConfigMock = $this->createMock(ProfileConfigInterface::class);

        $importProcessMock->expects($this->once())
            ->method('getEntityConfig')
            ->willReturn($entityConfigMock);
        $importProcessMock->expects($this->once())
            ->method('getProfileConfig')
            ->willReturn($profileConfigMock);
        $this->dataStructureProviderMock->expects($this->once())
            ->method('getDataStructure')
            ->with($entityConfigMock, $profileConfigMock)
            ->willReturn($dataStructureMock);

        $this->assertEquals(
            $expectedResult,
            $this->sourceDataProcessor->convertToImportProcessStructure($importProcessMock, $row)
        );
    }

    /**
     * @return array
     */
    public function convertToImportProcessStructureDataProvider()
    {
        return [
            [
                ['field' => 'value'],
                $this->createConfiguredMock(
                    SourceDataStructureInterface::class,
                    ['getSubEntityStructures' => []]
                ),
                ['field' => 'value']
            ],
            [
                [
                    'field' => 'value',
                    'sub_entity' => [
                        ['sub_entity_field' => 'sub_entity_value']
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructureInterface::class,
                    [
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructureInterface::class,
                                [
                                    'getMap' => 'sub_entity',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                [
                    'field' => 'value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity' => [
                            ['sub_entity_field' => 'sub_entity_value']
                        ]
                    ]
                ]
            ],
            [
                [
                    'field' => 'value',
                    'sub_entity' => [
                        ['sub_entity_field' => ''],
                        ['sub_entity_field' => 'sub_entity_value']
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructureInterface::class,
                    [
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructureInterface::class,
                                [
                                    'getMap' => 'sub_entity',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                [
                    'field' => 'value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity' => [
                            ['sub_entity_field' => 'sub_entity_value']
                        ]
                    ]
                ]
            ]
        ];
    }
}
