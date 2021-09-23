<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Source\Data;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface as ProfileEntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Source\Data\DataStructureBuilder;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Amasty\ImportCore\Import\Source\SourceDataStructureFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Source\Data\DataStructureProvider
 */
class DataStructureProviderTest extends \PHPUnit\Framework\TestCase
{
    const ENTITY_CODE = 'entity';
    const SUB_ENTITY_CODE = 'sub_entity';

    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    /**
     * @var RelationConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $relationConfigProviderMock;

    /**
     * @var DataStructureBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataStructureBuilderMock;

    /**
     * @var SourceDataStructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataStructureFactoryMock;

    /**
     * @var EntityConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityConfigMock;

    /**
     * @var ProfileConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileConfigMock;

    /**
     * @var ProfileEntitiesConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileEntitiesConfigMock;

    /**
     * @var SourceDataStructureInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataStructureMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->relationConfigProviderMock = $this->createMock(RelationConfigProvider::class);
        $this->dataStructureBuilderMock = $this->createMock(DataStructureBuilder::class);
        $this->dataStructureFactoryMock = $this->createPartialMock(
            SourceDataStructureFactory::class,
            ['create']
        );

        $this->entityConfigMock = $this->createMock(EntityConfigInterface::class);
        $this->profileConfigMock = $this->createMock(ProfileConfigInterface::class);
        $this->profileEntitiesConfigMock = $this->createMock(ProfileEntitiesConfigInterface::class);
        $this->dataStructureMock = $this->createMock(SourceDataStructureInterface::class);

        $this->dataStructureProvider = $objectManager->getObject(
            DataStructureProvider::class,
            [
                'relationConfigProvider' => $this->relationConfigProviderMock,
                'dataStructureBuilder' => $this->dataStructureBuilderMock,
                'dataStructureFactory' => $this->dataStructureFactoryMock
            ]
        );
    }

    public function testGetDataStructureWithoutSubEntity()
    {
        $data = [
            SourceDataStructure::ENTITY_CODE => self::ENTITY_CODE,
            SourceDataStructure::FIELDS => ['field1', 'field2'],
            SourceDataStructure::SUB_ENTITY_STRUCTURES => [],
            SourceDataStructure::MAP => 'entity_prefix'
        ];

        $this->profileConfigMock->expects($this->once())
            ->method('getEntitiesConfig')
            ->willReturn($this->profileEntitiesConfigMock);
        $this->profileEntitiesConfigMock->expects($this->once())
            ->method('getSubEntitiesConfig')
            ->willReturn([]);
        $this->profileConfigMock->expects($this->once())
            ->method('getEntityCode')
            ->willReturn(self::ENTITY_CODE);
        $this->relationConfigProviderMock->expects($this->once())
            ->method('get')
            ->with(self::ENTITY_CODE)
            ->willReturn([]);
        $this->dataStructureBuilderMock->expects($this->once())
            ->method('buildEntityDataStructure')
            ->with(
                $this->entityConfigMock,
                $this->profileConfigMock,
                []
            )
            ->willReturn($data);
        $this->dataStructureFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $data])
            ->willReturn($this->dataStructureMock);

        $this->assertSame(
            $this->dataStructureMock,
            $this->dataStructureProvider->getDataStructure(
                $this->entityConfigMock,
                $this->profileConfigMock
            )
        );
    }

    public function testGetDataStructureWithSubEntity()
    {
        $subEntityDataStructure = $this->createMock(SourceDataStructureInterface::class);
        $data = [
            SourceDataStructure::ENTITY_CODE => self::ENTITY_CODE,
            SourceDataStructure::FIELDS => ['field1', 'field2'],
            SourceDataStructure::SUB_ENTITY_STRUCTURES => [$subEntityDataStructure],
            SourceDataStructure::MAP => 'entity_prefix'
        ];
        $subEntityMap = 'sub_entity_prefix';
        $subEntityData = [
            SourceDataStructure::ENTITY_CODE => self::SUB_ENTITY_CODE,
            SourceDataStructure::ID_FIELD_NAME => 'sub_entity_id',
            SourceDataStructure::PARENT_ID_FIELD_NAME => 'entity_id',
            SourceDataStructure::MAP => $subEntityMap,
            SourceDataStructure::FIELDS => ['sub_field1', 'sub_field2'],
            SourceDataStructure::SUB_ENTITY_STRUCTURES => []
        ];
        $profileSubEntitiesConfigMock = $this->createMock(ProfileEntitiesConfigInterface::class);
        $relationConfigMock = $this->createMock(RelationConfigInterface::class);

        $this->profileConfigMock->expects($this->once())
            ->method('getEntitiesConfig')
            ->willReturn($this->profileEntitiesConfigMock);
        $this->profileEntitiesConfigMock->expects($this->once())
            ->method('getSubEntitiesConfig')
            ->willReturn([$profileSubEntitiesConfigMock]);
        $profileSubEntitiesConfigMock->expects($this->once())
            ->method('getEntityCode')
            ->willReturn(self::SUB_ENTITY_CODE);
        $this->profileConfigMock->expects($this->once())
            ->method('getEntityCode')
            ->willReturn(self::ENTITY_CODE);
        $this->relationConfigProviderMock->expects($this->once())
            ->method('get')
            ->with(self::ENTITY_CODE)
            ->willReturn([$relationConfigMock]);
        $relationConfigMock->expects($this->once())
            ->method('getChildEntityCode')
            ->willReturn(self::SUB_ENTITY_CODE);
        $relationConfigMock->expects($this->once())
            ->method('getRelations')
            ->willReturn(null);
        $profileSubEntitiesConfigMock->expects($this->once())
            ->method('getSubEntitiesConfig')
            ->willReturn([]);
        $this->dataStructureBuilderMock->expects($this->once())
            ->method('buildSubEntityDataStructure')
            ->with($profileSubEntitiesConfigMock, $relationConfigMock, [])
            ->willReturn($subEntityData);
        $subEntityDataStructure->expects($this->once())
            ->method('getMap')
            ->willReturn($subEntityMap);

        $this->dataStructureFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [['data' => $subEntityData], $subEntityDataStructure],
                    [['data' => $data], $this->dataStructureMock]
                ]
            );
        $this->dataStructureBuilderMock->expects($this->once())
            ->method('buildEntityDataStructure')
            ->with(
                $this->entityConfigMock,
                $this->profileConfigMock,
                [$subEntityMap => $subEntityDataStructure]
            )
            ->willReturn($data);

        $this->assertSame(
            $this->dataStructureMock,
            $this->dataStructureProvider->getDataStructure(
                $this->entityConfigMock,
                $this->profileConfigMock
            )
        );
    }

    public function testGetDataStructureCaching()
    {
        $reflection = new \ReflectionClass(get_class($this->dataStructureProvider));
        $reflectionProperty = $reflection->getProperty('dataStructure');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->dataStructureProvider, $this->dataStructureMock);

        $this->dataStructureBuilderMock->expects($this->never())
            ->method('buildEntityDataStructure');
        $this->dataStructureFactoryMock->expects($this->never())
            ->method('create');

        $this->assertSame(
            $this->dataStructureMock,
            $this->dataStructureProvider->getDataStructure(
                $this->entityConfigMock,
                $this->profileConfigMock
            )
        );
    }
}
