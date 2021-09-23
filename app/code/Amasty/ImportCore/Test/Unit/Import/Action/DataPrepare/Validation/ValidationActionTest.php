<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Action\DataPrepare\Validation;

use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Validation\RowValidatorInterface;
use Amasty\ImportCore\Import\Action\DataPrepare\Validation\FieldValidator;
use Amasty\ImportCore\Import\Action\DataPrepare\Validation\ValidationAction;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\ImportCore\Import\Action\DataPrepare\Validation\ValidationAction
 */
class ValidationActionTest extends \PHPUnit\Framework\TestCase
{
    const ROW_NUMBER = 0;

    /**
     * @var ValidationAction
     */
    private $validationAction;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->validationAction = $objectManager->getObject(ValidationAction::class);
    }

    /**
     * @param array $row
     * @param FieldValidator[]|\PHPUnit_Framework_MockObject_MockObject[] $fieldRuleMocks
     * @param FieldValidator[][]|\PHPUnit_Framework_MockObject_MockObject[][] $fieldRuleRegistryMocks
     * @param SourceDataStructure|\PHPUnit_Framework_MockObject_MockObject $dataStructureMock
     * @param bool $expectedResult
     * @param string|null $expectedMessage
     * @dataProvider isFieldsDataValidDataProvider
     */
    public function testIsFieldsDataValid(
        array $row,
        array $fieldRuleMocks,
        array $fieldRuleRegistryMocks,
        $dataStructureMock,
        $expectedResult,
        $expectedMessage = null
    ) {
        $importProcessMock = $this->createMock(ImportProcessInterface::class);
        if ($expectedMessage) {
            $importProcessMock->expects($this->once())
                ->method('addValidationError')
                ->with($expectedMessage);
        }

        $reflection = new \ReflectionClass(ValidationAction::class);

        $fieldRulesRegistryProperty = $reflection->getProperty('fieldRulesRegistry');
        $fieldRulesRegistryProperty->setAccessible(true);
        $fieldRulesRegistryProperty->setValue(
            $this->validationAction,
            $fieldRuleRegistryMocks
        );

        $method = $reflection->getMethod('isFieldsDataValid');
        $method->setAccessible(true);

        $this->assertEquals(
            $expectedResult,
            $method->invokeArgs(
                $this->validationAction,
                [
                    $importProcessMock,
                    $row,
                    $dataStructureMock,
                    $fieldRuleMocks,
                    self::ROW_NUMBER
                ]
            )
        );
    }

    /**
     * @param array $row
     * @param RowValidatorInterface|\PHPUnit_Framework_MockObject_MockObject|null $rowRuleMock
     * @param RowValidatorInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $rowRuleRegistryMocks
     * @param bool $expectedResult
     * @param string|null $expectedMessage
     * @dataProvider isRowDataValidDataProvider
     */
    public function testIsRowDataValid(
        array $row,
        $rowRuleMock,
        array $rowRuleRegistryMocks,
        $expectedResult,
        $expectedMessage = null
    ) {
        $importProcessMock = $this->createMock(ImportProcessInterface::class);
        if ($expectedMessage) {
            $importProcessMock->expects($this->once())
                ->method('addValidationError')
                ->with($expectedMessage);
        }

        $reflection = new \ReflectionClass(ValidationAction::class);

        $rowRulesRegistryProperty = $reflection->getProperty('rowRulesRegistry');
        $rowRulesRegistryProperty->setAccessible(true);
        $rowRulesRegistryProperty->setValue(
            $this->validationAction,
            $rowRuleRegistryMocks
        );

        $method = $reflection->getMethod('isRowDataValid');
        $method->setAccessible(true);

        $this->assertEquals(
            $expectedResult,
            $method->invokeArgs(
                $this->validationAction,
                [$importProcessMock, $row, self::ROW_NUMBER, $rowRuleMock]
            )
        );
    }

    /**
     * Create field validator mock
     *
     * @param array $row
     * @param string $fieldName
     * @param bool $expectedResult
     * @param string|null $errorMessage
     * @return FieldValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldValidatorMock(
        array $row,
        $fieldName,
        $expectedResult,
        $errorMessage = null
    ) {
        /** @var FieldValidator|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->createMock(FieldValidator::class);
        $mock->expects($this->any())
            ->method('validate')
            ->with($row, $fieldName)
            ->willReturn($expectedResult);

        if ($errorMessage) {
            $mock->expects($this->once())
                ->method('getErrorMessage')
                ->willReturn($errorMessage);
        }

        return $mock;
    }

    /**
     * Create row validator mock
     *
     * @param array $row
     * @param bool $expectedResult
     * @param string|null $errorMessage
     * @return RowValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRowValidatorMock(array $row, $expectedResult, $errorMessage = null)
    {
        /** @var RowValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->createMock(RowValidatorInterface::class);
        $mock->expects($this->any())
            ->method('validate')
            ->with($row)
            ->willReturn($expectedResult);

        if ($errorMessage) {
            $mock->expects($this->once())
                ->method('getMessage')
                ->willReturn($errorMessage);
        }

        return $mock;
    }

    /**
     * @return array
     */
    public function isFieldsDataValidDataProvider()
    {
        return [
            [
                ['field_name' => 'field_value'],
                [],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                true,
                null
            ],
            [
                ['field_name' => 'field_value'],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            ['field_name' => 'field_value'],
                            'field_name',
                            true
                        )
                    ]
                ],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                true,
                null
            ],
            [
                ['field_name' => 'field_value'],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            ['field_name' => 'field_value'],
                            'field_name',
                            false,
                            'field_name value is incorrect.'
                        )
                    ]
                ],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                false,
                'field_name value is incorrect.'
            ],
            [
                ['field_name' => 'field_value'],
                [
                    'another_field_name' => [
                        $this->createFieldValidatorMock(
                            ['field_name' => 'field_value'],
                            'another_field_name',
                            true
                        )
                    ]
                ],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                true,
                null
            ],
            [
                ['field_name' => 'field_value'],
                [
                    'another_field_name' => [
                        $this->createFieldValidatorMock(
                            ['field_name' => 'field_value'],
                            'another_field_name',
                            true
                        )
                    ]
                ],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['another_field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [],
                [],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => []
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            [
                                'field_name' => 'field_value',
                                SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                                    'sub_entity_code' => [
                                        ['sub_entity_field_name' => 'sub_entity_field_value']
                                    ]
                                ]
                            ],
                            'field_name',
                            true
                        )
                    ]
                ],
                [
                    'sub_entity_code' => [
                        'sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'sub_entity_field_name',
                                true
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            [
                                'field_name' => 'field_value',
                                SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                                    'sub_entity_code' => [
                                        ['sub_entity_field_name' => 'sub_entity_field_value']
                                    ]
                                ]
                            ],
                            'field_name',
                            true
                        )
                    ]
                ],
                [
                    'sub_entity_code' => [
                        'sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'sub_entity_field_name',
                                false,
                                'sub_entity_field_name value is incorrect.'
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                false,
                'sub_entity_field_name value is incorrect.'
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            [
                                'field_name' => 'field_value',
                                SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                                    'sub_entity_code' => [
                                        ['sub_entity_field_name' => 'sub_entity_field_value']
                                    ]
                                ]
                            ],
                            'field_name',
                            true
                        )
                    ]
                ],
                [
                    'sub_entity_code' => [
                        'another_sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'another_sub_entity_field_name',
                                true
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [
                    'field_name' => [
                        $this->createFieldValidatorMock(
                            [
                                'field_name' => 'field_value',
                                SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                                    'sub_entity_code' => [
                                        ['sub_entity_field_name' => 'sub_entity_field_value']
                                    ]
                                ]
                            ],
                            'field_name',
                            true
                        )
                    ]
                ],
                [
                    'another_sub_entity_code' => [
                        'sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'sub_entity_field_value',
                                true
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [],
                [
                    'sub_entity_code' => [
                        'sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'sub_entity_field_name',
                                true
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                [],
                [
                    'sub_entity_code' => [
                        'sub_entity_field_name' => [
                            $this->createFieldValidatorMock(
                                ['sub_entity_field_name' => 'sub_entity_field_value'],
                                'sub_entity_field_name',
                                false,
                                'sub_entity_field_name value is incorrect.'
                            )
                        ]
                    ]
                ],
                $this->createConfiguredMock(
                    SourceDataStructure::class,
                    [
                        'getEntityCode' => 'entity_code',
                        'getFields' => ['field_name'],
                        'getSubEntityStructures' => [
                            $this->createConfiguredMock(
                                SourceDataStructure::class,
                                [
                                    'getFields' => ['sub_entity_field_name'],
                                    'getEntityCode' => 'sub_entity_code',
                                    'getSubEntityStructures' => []
                                ]
                            )
                        ]
                    ]
                ),
                false,
                'sub_entity_field_name value is incorrect.'
            ]
        ];
    }

    /**
     * @return array
     */
    public function isRowDataValidDataProvider()
    {
        return [
            [
                ['field_name' => 'field_value'],
                null,
                [],
                true,
                null
            ],
            [
                ['field_name' => 'field_value'],
                $this->createRowValidatorMock(['field_name' => 'field_value'], true),
                [],
                true,
                null
            ],
            [
                ['field_name' => 'field_value'],
                $this->createRowValidatorMock(
                    ['field_name' => 'field_value'],
                    false,
                    'Row is incorrect.'
                ),
                [],
                false,
                'Row is incorrect.'
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                $this->createRowValidatorMock(
                    [
                        'field_name' => 'field_value',
                        SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                            'sub_entity_code' => [
                                ['sub_entity_field_name' => 'sub_entity_field_value']
                            ]
                        ]
                    ],
                    true
                ),
                [
                    'sub_entity_code' => $this->createRowValidatorMock(
                        ['sub_entity_field_name' => 'sub_entity_field_value'],
                        true
                    )
                ],
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                $this->createRowValidatorMock(
                    [
                        'field_name' => 'field_value',
                        SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                            'sub_entity_code' => [
                                ['sub_entity_field_name' => 'sub_entity_field_value']
                            ]
                        ]
                    ],
                    true
                ),
                [
                    'sub_entity_code' => $this->createRowValidatorMock(
                        ['sub_entity_field_name' => 'sub_entity_field_value'],
                        false,
                        'Sub entity row is incorrect.'
                    )
                ],
                false,
                'Sub entity row is incorrect.'
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                null,
                [
                    'sub_entity_code' => $this->createRowValidatorMock(
                        ['sub_entity_field_name' => 'sub_entity_field_value'],
                        true
                    )
                ],
                true,
                null
            ],
            [
                [
                    'field_name' => 'field_value',
                    SourceDataStructure::SUB_ENTITIES_DATA_KEY => [
                        'sub_entity_code' => [
                            ['sub_entity_field_name' => 'sub_entity_field_value']
                        ]
                    ]
                ],
                null,
                [
                    'sub_entity_code' => $this->createRowValidatorMock(
                        ['sub_entity_field_name' => 'sub_entity_field_value'],
                        false,
                        'Sub entity row is incorrect.'
                    )
                ],
                false,
                'Sub entity row is incorrect.'
            ]
        ];
    }
}
