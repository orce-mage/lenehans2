<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Test\Unit\Export\Template\Type\Csv\Utils;

/**
 * @covers \Amasty\ExportCore\Export\Template\Type\Csv\Utils\ConvertRowTo2DimensionalArray
 */
class ConvertRowTo2DimensionalArrayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'col-third' => 'third'
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'col-third' => '',
                ],
                [
                    [
                        'first',
                        'second',
                        'third'
                    ]
                ]
            ],
            'multi' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth'
                        ]
                    ],
                    'items-2' => [
                        [
                            'col-fifth' => 'fifth',
                            'col-sixth' => 'sixth',
                            'col-seventh' => 'seventh',
                        ],
                        [
                            'col-fifth' => 'fifth1',
                            'col-sixth' => 'sixth1',
                            'col-seventh' => 'seventh1',
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ],
                    'items-2' => [
                        'col-fifth' => '',
                        'col-sixth' => '',
                        'col-seventh' => ''
                    ]
                ],
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        'sixth',
                        'seventh'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1'
                    ]
                ]
            ],
            'multi-2' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items-1' => [
                                [
                                    'col-third-1' => 'third-1',
                                    'col-fourth-1' => 'fourth-1'
                                ],
                                [
                                    'col-third-1' => 'third-2',
                                    'col-fourth-1' => 'fourth-2'
                                ]
                            ]
                        ]
                    ],
                    'items-2' => [
                        [
                            'col-fifth' => 'fifth',
                            'col-sixth' => 'sixth',
                            'col-seventh' => 'seventh',
                            'items-2-1' => [
                                [
                                    'col-third-2-1' => 'third-2-1',
                                    'col-fourth-2-1' => 'fourth-2-1'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-2',
                                    'col-fourth-2-1' => 'fourth-2-2'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-3',
                                    'col-fourth-2-1' => 'fourth-2-3'
                                ],
                                [
                                    'col-third-2-1' => 'third-2-4',
                                    'col-fourth-2-1' => 'fourth-2-4'
                                ]
                            ]
                        ],
                        [
                            'col-fifth' => 'fifth1',
                            'col-sixth' => 'sixth1',
                            'col-seventh' => 'seventh1',
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items-1' => [
                            'col-third-1' => '',
                            'col-fourth-1' => '',
                        ]
                    ],
                    'items-2' => [
                        'col-fifth' => '',
                        'col-sixth' => '',
                        'col-seventh' => '',
                        'items-2-1' => [
                            'col-third-2-1' => '',
                            'col-fourth-2-1' => ''
                        ]
                    ]
                ],
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'third-1',
                        'fourth-1',
                        'fifth',
                        'sixth',
                        'seventh',
                        'third-2-1',
                        'fourth-2-1'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'third-2',
                        'fourth-2',
                        '',
                        '',
                        '',
                        'third-2-2',
                        'fourth-2-2'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'third-2-3',
                        'fourth-2-3'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'third-2-4',
                        'fourth-2-4'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1',
                        '',
                        ''
                    ]
                ]
            ],
            'multi-with-empty-cells' => [
                [
                    'items' => [
                        [
                            'col-third' => 'third'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ]
                ],
                [
                    [
                        '',
                        '',
                        'third',
                        ''
                    ]
                ]
            ],
            'subentity-with-multiple-rows' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth'
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => ''
                    ]
                ],
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1'
                    ]
                ]
            ],
            'multiple-subentity-with-multiple-rows' => [
                [
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth',
                                    'col-sixth' => 'sixth',
                                    'col-seventh' => 'seventh',
                                ],
                                [
                                    'col-fifth' => 'fifth1',
                                    'col-sixth' => 'sixth1',
                                    'col-seventh' => 'seventh1',
                                ],
                                [
                                    'col-fifth' => 'fifth2',
                                    'col-sixth' => 'sixth2',
                                    'col-seventh' => 'seventh2',
                                ]
                            ]
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1'
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items1' => [
                            'col-fifth' => '',
                            'col-sixth' => '',
                            'col-seventh' => ''
                        ]
                    ]
                ],
                [
                    [
                        '',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        'sixth',
                        'seventh'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        'seventh1'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth2',
                        'sixth2',
                        'seventh2'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1',
                        '',
                        '',
                        ''
                    ]
                ]
            ],
            'custom-final-test' => [
                [
                    'col-first' => 'first',
                    'col-second' => 'second',
                    'items' => [
                        [
                            'col-third' => 'third',
                            'col-fourth' => 'fourth',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth',
                                ],
                                [
                                    'col-fifth' => 'fifth1',
                                    'col-sixth' => 'sixth1',
                                ],
                                [
                                    'col-fifth' => 'fifth2',
                                    'col-sixth' => 'sixth2',
                                    'col-seventh' => 'seventh2',
                                ]
                            ]
                        ],
                        [
                            'col-third' => 'third1',
                            'col-fourth' => 'fourth1',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth12',
                                    'col-sixth' => 'sixth12',
                                    'col-seventh' => 'seventh12',
                                ]
                            ]
                        ],
                        [
                            'col-fourth' => 'fourth2',
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth21',
                                    'col-sixth' => 'sixth21',
                                    'col-seventh' => 'seventh21',
                                ],
                                [
                                    'col-fifth' => 'fifth22',
                                    'col-sixth' => 'sixth22',
                                    'col-seventh' => 'seventh22',
                                ],
                                [
                                    'col-fifth' => 'fifth23',
                                    'col-seventh' => 'seventh23',
                                ],
                                [
                                    'col-fifth' => 'fifth24',
                                    'col-sixth' => 'sixth24',
                                    'col-seventh' => 'seventh24',
                                ]
                            ]
                        ],
                        [
                            'items1' => [
                                [
                                    'col-fifth' => 'fifth31',
                                    'col-sixth' => 'sixth31',
                                    'col-seventh' => 'seventh31',
                                ],
                                [
                                    'col-fifth' => 'fifth32',
                                    'col-sixth' => 'sixth32',
                                    'col-seventh' => 'seventh32',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'col-first' => '',
                    'col-second' => '',
                    'items' => [
                        'col-third' => '',
                        'col-fourth' => '',
                        'items1' => [
                            'col-fifth' => '',
                            'col-sixth' => '',
                            'col-seventh' => ''
                        ]
                    ]
                ],
                [
                    [
                        'first',
                        'second',
                        'third',
                        'fourth',
                        'fifth',
                        '',
                        ''
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth1',
                        'sixth1',
                        ''
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth2',
                        'sixth2',
                        'seventh2'
                    ],
                    [
                        '',
                        '',
                        'third1',
                        'fourth1',
                        'fifth12',
                        'sixth12',
                        'seventh12'
                    ],
                    [
                        '',
                        '',
                        '',
                        'fourth2',
                        'fifth21',
                        'sixth21',
                        'seventh21'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth22',
                        'sixth22',
                        'seventh22'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth23',
                        '',
                        'seventh23'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth24',
                        'sixth24',
                        'seventh24'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth31',
                        'sixth31',
                        'seventh31'
                    ],
                    [
                        '',
                        '',
                        '',
                        '',
                        'fifth32',
                        'sixth32',
                        'seventh32'
                    ],
                ]
            ],
        ];
    }

    /**
     * @param array $row
     * @param array $headerStructure
     * @param $expectedResult
     *
     * @dataProvider validateDataProvider
     */
    public function testConvert(array $row, array $headerStructure, $expectedResult)
    {
        $converter = new \Amasty\ExportCore\Export\Template\Type\Csv\Utils\ConvertRowTo2DimensionalArray();
        $this->assertSame($expectedResult, $converter->convert($row, $headerStructure));
    }
}
