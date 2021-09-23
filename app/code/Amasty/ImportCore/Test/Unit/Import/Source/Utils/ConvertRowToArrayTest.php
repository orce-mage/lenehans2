<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\Source\Utils;

use Amasty\ImportCore\Import\Source\Utils\ConvertRowToArray;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConvertRowToArrayTest extends TestCase
{
    /**
     * @var ConvertRowToArray
     */
    private $converter;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->converter = $objectManager->getObject(ConvertRowToArray::class);
    }

    /**
     * @param array $data
     * @param array $expectedResult
     * @dataProvider initHeaderStructureDataProvider
     */
    public function testInitHeaderStructure(array $data, array $expectedResult)
    {
        $reflection = new \ReflectionClass(ConvertRowToArray::class);

        $method = $reflection->getMethod('initHeaderStructure');
        $method->setAccessible(true);
        $headerStructure = $method->invokeArgs($this->converter, [$data]);

        $this->assertEquals($expectedResult, $headerStructure);
    }

    /**
     * @param array $destStructure
     * @param array $sourceStructure
     * @param array $expectedResult
     * @dataProvider mergeRowStructuresDataProvider
     */
    public function testMergeRowStructures(
        array $destStructure,
        array $sourceStructure,
        array $expectedResult
    ) {
        $reflection = new \ReflectionClass(ConvertRowToArray::class);

        $method = $reflection->getMethod('mergeRowStructures');
        $method->setAccessible(true);
        $mergedStructure = $method->invokeArgs(
            $this->converter,
            [$destStructure, $sourceStructure]
        );

        $this->assertEquals($expectedResult, $mergedStructure);
    }

    public function initHeaderStructureDataProvider()
    {
        return [
            [
                [
                    [
                        'ent1Field1' => 'value11',
                        'ent1Field2' => 'value12',
                        'subEntity1' => [
                            [
                                'subEnt1Field1' => 'subValue11',
                                'subEnt1Field2' => 'subValue12'
                            ]
                        ],
                        'subEntity2' => [
                            [
                                'subEnt2Field1' => 'subValue21',
                                'subEnt2Field2' => 'subValue22'
                            ]
                        ]
                    ],
                    [
                        'ent1Field1' => 'value21',
                        'ent1Field2' => 'value22',
                        'subEntity1' => [
                            [
                                'subEnt1Field1' => 'subValue11',
                                'subEnt1Field2' => 'subValue12'
                            ]
                        ],
                        'subEntity3' => [
                            [
                                'subEnt3Field1' => 'subValue31',
                                'subEnt3Field2' => 'subValue32'
                            ]
                        ]
                    ]
                ],
                [
                    'ent1Field1' => '',
                    'ent1Field2' => '',
                    'subEntity1' => [
                        'subEnt1Field1' => '',
                        'subEnt1Field2' => ''
                    ],
                    'subEntity2' => [
                        'subEnt2Field1' => '',
                        'subEnt2Field2' => ''
                    ],
                    'subEntity3' => [
                        'subEnt3Field1' => '',
                        'subEnt3Field2' => ''
                    ]
                ]
            ],
            [
                [
                    [
                        'ent1Field1' => 'value11',
                        'ent1Field2' => 'value12',
                        'subEntity1' => [
                            [
                                'subEnt1Field1' => 'subValue11',
                                'subEnt1Field2' => 'subValue12'
                            ]
                        ],
                        'subEntity2' => [
                            [
                                'subEnt2Field1' => 'subValue21',
                                'subEnt2Field2' => 'subValue22'
                            ]
                        ]
                    ],
                    [
                        'ent1Field1' => 'value21',
                        'ent1Field3' => 'value23',
                        'subEntity1' => [
                            ['subEnt1Field1' => 'subValue11']
                        ],
                        'subEntity2' => [
                            [
                                'subEnt2Field1' => 'subValue21',
                                'subEnt2Field3' => 'subValue23'
                            ]
                        ]
                    ]
                ],
                [
                    'ent1Field1' => '',
                    'ent1Field2' => '',
                    'ent1Field3' => '',
                    'subEntity1' => [
                        'subEnt1Field1' => '',
                        'subEnt1Field2' => ''
                    ],
                    'subEntity2' => [
                        'subEnt2Field1' => '',
                        'subEnt2Field2' => '',
                        'subEnt2Field3' => ''
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function mergeRowStructuresDataProvider()
    {
        return [
            [
                [
                    'key1' => '',
                    'key2' => ''
                ],
                [
                    'key1' => '',
                    'key3' => ''
                ],
                [
                    'key1' => '',
                    'key2' => '',
                    'key3' => ''
                ]
            ],
            [
                [
                    'key1' => '',
                    'key2' => [
                        'key21' => ''
                    ]
                ],
                [
                    'key1' => '',
                    'key2' => [
                        'key21' => '',
                        'key22' => ''
                    ]
                ],
                [
                    'key1' => '',
                    'key2' => [
                        'key21' => '',
                        'key22' => ''
                    ]
                ]
            ],
            [
                [
                    'key1' => [
                        'key11' => '',
                        'key12' => ''
                    ]
                ],
                [
                    'key1' => [
                        'key11' => '',
                        'key12' => '',
                        'key13' => ''
                    ],
                    'key2' => [
                        'key21' => '',
                        'key22' => ''
                    ]
                ],
                [
                    'key1' => [
                        'key11' => '',
                        'key12' => '',
                        'key13' => ''
                    ],
                    'key2' => [
                        'key21' => '',
                        'key22' => ''
                    ]
                ]
            ]
        ];
    }
}
