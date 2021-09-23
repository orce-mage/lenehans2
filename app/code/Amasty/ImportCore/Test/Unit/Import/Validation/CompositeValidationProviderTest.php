<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Validation;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Amasty\ImportCore\Api\Validation\ValidationProviderInterface;
use Amasty\ImportCore\Import\Validation\CompositeValidationProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\ImportCore\Import\Validation\CompositeValidationProvider
 */
class CompositeValidationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompositeValidationProvider
     */
    private $compositeProvider;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->compositeProvider = $objectManager->getObject(CompositeValidationProvider::class);
    }

    /**
     * @param ValidationProviderInterface[]|MockObject[] $providerMocks
     * @param string $entityCode
     * @param array $expectedResult
     * @dataProvider getFieldValidatorsDataProvider
     */
    public function testGetFieldValidators(array $providerMocks, $entityCode, array $expectedResult)
    {
        /** @var ImportProcessInterface|MockObject $importProcessMock */
        $importProcessMock = $this->createMock(ImportProcessInterface::class);
        $profileConfigMock = $this->createMock(ProfileConfigInterface::class);
        $entitiesConfigMock = $this->createMock(EntitiesConfigInterface::class);

        $importProcessMock->expects($this->once())
            ->method('getProfileConfig')
            ->willReturn($profileConfigMock);
        $profileConfigMock->expects($this->once())
            ->method('getEntitiesConfig')
            ->willReturn($entitiesConfigMock);
        $entitiesConfigMock->expects($this->once())
            ->method('getEntityCode')
            ->willReturn($entityCode);

        $this->setMockedProperties(
            $this->compositeProvider,
            ['providerInstancesByEntityCode' => [$entityCode => $providerMocks]]
        );

        $this->assertEquals(
            $expectedResult,
            $this->compositeProvider->getFieldValidators($importProcessMock)
        );
    }

    /**
     * Set mocked properties to the object
     *
     * @param object $object
     * @param array $properties
     * @return void
     * @throws \ReflectionException
     */
    private function setMockedProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }

    /**
     * Create validation provider mock
     *
     * @param string $methodName
     * @param array $arguments
     * @param callable $callback
     * @return MockObject
     */
    private function createValidationProviderMock($methodName, $arguments, $callback)
    {
        $providerMock = $this->createMock(ValidationProviderInterface::class);
        $providerMock->expects($this->once())
            ->method($methodName)
            ->with(...$arguments)
            ->willReturnCallback($callback);

        return $providerMock;
    }

    /**
     * @return array
     */
    public function getFieldValidatorsDataProvider()
    {
        $fieldValidatorMock1 = $this->createMock(FieldValidatorInterface::class);
        $fieldValidatorMock2 = $this->createMock(FieldValidatorInterface::class);

        return [
            [
                [
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [$this->anything(), []],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1) {
                            $validators['entity_code'] = ['field_name_1' => [$fieldValidatorMock1]];

                            return $validators;
                        }
                    ),
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [
                            $this->anything(),
                            ['entity_code' => ['field_name_1' => [$fieldValidatorMock1]]]
                        ],
                        function ($importProcess, &$validators) use ($fieldValidatorMock2) {
                            $validators['entity_code']['field_name_2'] = [$fieldValidatorMock2];

                            return $validators;
                        }
                    )
                ],
                'entity_code',
                [
                    'entity_code' => [
                        'field_name_1' => [$fieldValidatorMock1],
                        'field_name_2' => [$fieldValidatorMock2]
                    ]
                ]
            ],
            [
                [
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [$this->anything(), []],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1) {
                            $validators['entity_code'] = ['field_name_1' => [$fieldValidatorMock1]];

                            return $validators;
                        }
                    ),
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [
                            $this->anything(),
                            ['entity_code' => ['field_name_1' => [$fieldValidatorMock1]]]
                        ],
                        function ($importProcess, &$validators) {
                            return $validators;
                        }
                    )
                ],
                'entity_code',
                [
                    'entity_code' => ['field_name_1' => [$fieldValidatorMock1]]
                ]
            ],
            [
                [
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [$this->anything(), []],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1) {
                            $validators['entity_code'] = ['field_name_1' => [$fieldValidatorMock1]];

                            return $validators;
                        }
                    ),
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [
                            $this->anything(),
                            ['entity_code' => ['field_name_1' => [$fieldValidatorMock1]]]
                        ],
                        function ($importProcess, &$validators) use ($fieldValidatorMock2) {
                            $validators['entity_code']['field_name_1'][] = $fieldValidatorMock2;

                            return $validators;
                        }
                    )
                ],
                'entity_code',
                [
                    'entity_code' => ['field_name_1' => [$fieldValidatorMock1, $fieldValidatorMock2]]
                ]
            ],
            [
                [
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [$this->anything(), []],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1, $fieldValidatorMock2) {
                            $validators['entity_code'] = [
                                'field_name_1' => [$fieldValidatorMock1, $fieldValidatorMock2]
                            ];

                            return $validators;
                        }
                    ),
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [
                            $this->anything(),
                            ['entity_code' => ['field_name_1' => [$fieldValidatorMock1, $fieldValidatorMock2]]]
                        ],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1) {
                            $validators['entity_code']['field_name_1'] = [$fieldValidatorMock1];

                            return $validators;
                        }
                    )
                ],
                'entity_code',
                [
                    'entity_code' => ['field_name_1' => [$fieldValidatorMock1]]
                ]
            ],
            [
                [
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [$this->anything(), []],
                        function ($importProcess, &$validators) use ($fieldValidatorMock1, $fieldValidatorMock2) {
                            $validators['entity_code'] = [
                                'field_name_1' => [$fieldValidatorMock1],
                                'field_name_2' => [$fieldValidatorMock2]
                            ];

                            return $validators;
                        }
                    ),
                    $this->createValidationProviderMock(
                        'getFieldValidators',
                        [
                            $this->anything(),
                            [
                                'entity_code' => [
                                    'field_name_1' => [$fieldValidatorMock1],
                                    'field_name_2' => [$fieldValidatorMock2]
                                ]
                            ]
                        ],
                        function ($importProcess, &$validators) {
                            unset($validators['entity_code']['field_name_2']);

                            return $validators;
                        }
                    )
                ],
                'entity_code',
                [
                    'entity_code' => ['field_name_1' => [$fieldValidatorMock1]]
                ]
            ]
        ];
    }
}
