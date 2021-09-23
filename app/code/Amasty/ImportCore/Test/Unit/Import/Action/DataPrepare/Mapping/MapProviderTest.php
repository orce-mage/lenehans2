<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Action\DataPrepare\Mapping;

use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Action\DataPrepare\Mapping\MapProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Action\DataPrepare\Mapping\MapProvider
 */
class MapProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MapProvider
     */
    private $mapProvider;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->mapProvider = $objectManager->getObject(MapProvider::class);
    }

    /**
     * @param SourceDataStructureInterface|\PHPUnit_Framework_MockObject_MockObject $dataStructureMock
     * @param array $expectedResult
     * @dataProvider getSubEntitiesMapDataProvider
     */
    public function testGetSubEntitiesMap($dataStructureMock, $expectedResult)
    {
        $this->assertEquals(
            $expectedResult,
            $this->mapProvider->getSubEntitiesMap($dataStructureMock)
        );
    }

    /**
     * @return array
     */
    public function getSubEntitiesMapDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    SourceDataStructureInterface::class,
                    ['getSubEntityStructures' => []]
                ),
                []
            ],
            [
                $this->createConfiguredMock(
                    SourceDataStructureInterface::class,
                    [
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructureInterface::class,
                                [
                                    'getMap' => 'sub_entity',
                                    'getEntityCode' => 'sub_entity_code'
                                ]
                            )
                        ]
                    ]
                ),
                ['sub_entity' => 'sub_entity_code']
            ]
        ];
    }
}
