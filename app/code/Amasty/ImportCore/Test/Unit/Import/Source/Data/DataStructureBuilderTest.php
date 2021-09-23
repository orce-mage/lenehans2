<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Source\Data;

use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Source\Data\DataStructureBuilder;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\ImportCore\Import\Source\Data\DataStructureBuilder
 */
class DataStructureBuilderTest extends \PHPUnit\Framework\TestCase
{
    const ID_FIELD_NAME = 'test';

    /**
     * @var DataStructureBuilder
     */
    private $dataStructureBuilder;

    /**
     * @var EntityConfigProvider|MockObject
     */
    private $entityConfigProviderMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->entityConfigProviderMock = $this->createPartialMock(
            EntityConfigProvider::class,
            ['get']
        );
        $this->dataStructureBuilder = $objectManager->getObject(
            DataStructureBuilder::class,
            ['entityConfigProvider' => $this->entityConfigProviderMock]
        );
    }

    /**
     * @param EntityConfigInterface|MockObject $entityConfigMock
     * @param ProfileConfigInterface|MockObject $profileConfigMock
     * @param SourceDataStructureInterface[]|MockObject[] $subEntityStructureMocks
     * @param array $expectedResult
     * @dataProvider buildEntityDataStructureDataProvider
     */
    public function testBuildEntityDataStructure(
        $entityConfigMock,
        $profileConfigMock,
        $subEntityStructureMocks,
        $expectedResult
    ) {
        $this->assertEquals(
            $expectedResult,
            $this->dataStructureBuilder->buildEntityDataStructure(
                $entityConfigMock,
                $profileConfigMock,
                $subEntityStructureMocks
            )
        );
    }

    /**
     * @param string $entityCode
     * @param EntitiesConfigInterface|MockObject $profileSubEntityConfigMock
     * @param EntityConfigInterface|MockObject $subEntityConfigMock
     * @param RelationConfigInterface|MockObject $relationConfigMock
     * @param SourceDataStructureInterface[]|MockObject[] $subEntityStructureMocks
     * @param array $expectedResult
     * @dataProvider buildSubEntityDataStructureDataProvider
     */
    public function testBuildSubEntityDataStructure(
        $profileSubEntityConfigMock,
        $relationConfigMock,
        $subEntityStructureMocks,
        $expectedResult
    ) {
        $relationEntityConfig = $this->createConfiguredMock(
            EntityConfigInterface::class,
            [
                'getEntityCode' => 'related_entity',
                'getFieldsConfig' => $this->createConfiguredMock(
                    FieldsConfigInterface::class,
                    [
                        'getFields' => [
                            $this->createConfiguredMock(
                                FieldInterface::class,
                                [
                                    'getName' => self::ID_FIELD_NAME,
                                    'isIdentity' => true
                                ]
                            )
                        ]
                    ]
                )
            ]
        );
        $this->entityConfigProviderMock->expects($this->any())
            ->method('get')
            ->with('sub_entity')
            ->willReturn($relationEntityConfig);

        $this->assertEquals(
            $expectedResult,
            $this->dataStructureBuilder->buildSubEntityDataStructure(
                $profileSubEntityConfigMock,
                $relationConfigMock,
                $subEntityStructureMocks
            )
        );
    }

    /**
     * @return array
     */
    public function buildEntityDataStructureDataProvider()
    {
        $subEntityDataStructureMock = $this->createMock(SourceDataStructureInterface::class);
        $fieldsConfig = $this->createConfiguredMock(
            FieldsConfigInterface::class,
            [
                'getFields' => [
                    $this->createConfiguredMock(
                        FieldInterface::class,
                        [
                            'getName' => self::ID_FIELD_NAME,
                            'isIdentity' => true
                        ]
                    )
                ]
            ]
        );
        return [
            [
                $this->createConfiguredMock(
                    EntityConfigInterface::class,
                    [
                        'getEntityCode' => 'entity',
                        'getFieldsConfig' => $fieldsConfig
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileConfigInterface::class,
                    [
                        'getEntitiesConfig' => $this->createConfiguredMock(
                            EntitiesConfigInterface::class,
                            ['getMap' => null]
                        )
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'entity',
                    SourceDataStructure::FIELDS => [],
                    SourceDataStructure::FIELDS_MAP => [],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => [],
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntityConfigInterface::class,
                    [
                        'getEntityCode' => 'entity',
                        'getFieldsConfig' => $fieldsConfig
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileConfigInterface::class,
                    [
                        'getEntitiesConfig' => $this->createConfiguredMock(
                            EntitiesConfigInterface::class,
                            [
                                'getFields' => [
                                    $this->createConfiguredMock(
                                        \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                        ['getName' => 'field']
                                    )
                                ]
                            ]
                        )
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => [],
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntityConfigInterface::class,
                    [
                        'getEntityCode' => 'entity',
                        'getFieldsConfig' => $fieldsConfig
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileConfigInterface::class,
                    [
                        'getEntitiesConfig' => $this->createConfiguredMock(
                            EntitiesConfigInterface::class,
                            [
                                'getFields' => [
                                    $this->createConfiguredMock(
                                        \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                        ['getName' => 'field']
                                    )
                                ]
                            ]
                        )
                    ]
                ),
                [$subEntityDataStructureMock],
                [
                    SourceDataStructure::ENTITY_CODE => 'entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => [$subEntityDataStructureMock],
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntityConfigInterface::class,
                    [
                        'getEntityCode' => 'entity',
                        'getFieldsConfig' => $fieldsConfig
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileConfigInterface::class,
                    [
                        'getEntitiesConfig' => $this->createConfiguredMock(
                            EntitiesConfigInterface::class,
                            [
                                'getMap' => 'entity',
                                'getFields' => [
                                    $this->createConfiguredMock(
                                        \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                        ['getName' => 'field']
                                    )
                                ]
                            ]
                        )
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => [],
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::MAP => 'entity'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function buildSubEntityDataStructureDataProvider()
    {
        $subEntityDataStructureMock = $this->createMock(SourceDataStructureInterface::class);

        return [
            [
                $this->createConfiguredMock(
                    EntitiesConfigInterface::class,
                    [
                        'getEntityCode' => 'sub_entity',
                        'getMap' => null,
                        'getFields' => [
                            $this->createConfiguredMock(
                                \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                ['getName' => 'field']
                            )
                        ]
                    ]
                ),
                $this->createConfiguredMock(
                    RelationConfigInterface::class,
                    [
                        'getSubEntityFieldName' => 'sub_entity'
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'sub_entity',
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::PARENT_ID_FIELD_NAME => null,
                    SourceDataStructure::MAP => 'sub_entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => []
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntitiesConfigInterface::class,
                    [
                        'getEntityCode' => 'sub_entity',
                        'getMap' => 'sub_entity_prefix',
                        'getFields' => [
                            $this->createConfiguredMock(
                                \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                ['getName' => 'field']
                            )
                        ]
                    ]
                ),
                $this->createConfiguredMock(
                    RelationConfigInterface::class,
                    [
                        'getSubEntityFieldName' => 'sub_entity'
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'sub_entity',
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::PARENT_ID_FIELD_NAME => null,
                    SourceDataStructure::MAP => 'sub_entity_prefix',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => []
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntitiesConfigInterface::class,
                    [
                        'getEntityCode' => 'sub_entity',
                        'getMap' => null,
                        'getFields' => [
                            $this->createConfiguredMock(
                                \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                ['getName' => 'field']
                            )
                        ]
                    ]
                ),
                $this->createConfiguredMock(
                    RelationConfigInterface::class,
                    [
                        'getSubEntityFieldName' => 'sub_entity',
                        'getChildFieldName' => 'sub_entity_id'
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'sub_entity',
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::PARENT_ID_FIELD_NAME => 'sub_entity_id',
                    SourceDataStructure::MAP => 'sub_entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => []
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntitiesConfigInterface::class,
                    [
                        'getEntityCode' => 'sub_entity',
                        'getMap' => null,
                        'getFields' => [
                            $this->createConfiguredMock(
                                \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                ['getName' => 'field']
                            )
                        ]
                    ]
                ),
                $this->createConfiguredMock(
                    RelationConfigInterface::class,
                    [
                        'getSubEntityFieldName' => 'sub_entity',
                        'getParentFieldName' => 'parent_entity_id'
                    ]
                ),
                [],
                [
                    SourceDataStructure::ENTITY_CODE => 'sub_entity',
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::PARENT_ID_FIELD_NAME => null,
                    SourceDataStructure::MAP => 'sub_entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => []
                ]
            ],
            [
                $this->createConfiguredMock(
                    EntitiesConfigInterface::class,
                    [
                        'getEntityCode' => 'sub_entity',
                        'getMap' => null,
                        'getFields' => [
                            $this->createConfiguredMock(
                                \Amasty\ImportCore\Api\Config\Profile\FieldInterface::class,
                                ['getName' => 'field']
                            )
                        ]
                    ]
                ),
                $this->createConfiguredMock(
                    RelationConfigInterface::class,
                    [
                        'getSubEntityFieldName' => 'sub_entity'
                    ]
                ),
                [$subEntityDataStructureMock],
                [
                    SourceDataStructure::ENTITY_CODE => 'sub_entity',
                    SourceDataStructure::ID_FIELD_NAME => self::ID_FIELD_NAME,
                    SourceDataStructure::PARENT_ID_FIELD_NAME => null,
                    SourceDataStructure::MAP => 'sub_entity',
                    SourceDataStructure::FIELDS => ['field'],
                    SourceDataStructure::FIELDS_MAP => ['field' => null],
                    SourceDataStructure::SUB_ENTITY_STRUCTURES => [$subEntityDataStructureMock]
                ]
            ]
        ];
    }
}
