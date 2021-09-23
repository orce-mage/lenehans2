<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Modifier;

use Amasty\Stockstatus\Model\Source\StockStatus as StockStatusSource;
use Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Data\Range\RangeProviderInterface;
use Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Meta\Range\AdditionalColumnsInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Ranges implements ModifierInterface
{
    const FIELD_ID = 'id';
    const FIELD_FROM = 'qty_from';
    const FIELD_TO = 'qty_to';
    const FIELD_STOCK_STATUS = 'status_id';
    const FIELD_SORT_ORDER_NAME = 'sort_order';
    const FIELD_IS_DELETE = 'is_delete';

    const GRID_RANGES = 'ranges';
    const BUTTON_ADD = 'button_add';

    /**
     * @var RangeProviderInterface
     */
    private $rangeProvider;

    /**
     * @var StockStatusSource
     */
    private $stockStatusSource;

    /**
     * @var string
     */
    private $containerName;

    /**
     * @var string
     */
    private $tabName;

    /**
     * @var string
     */
    private $dataScope;

    /**
     * @var AdditionalColumnsInterface|null
     */
    private $additionalColumns;

    public function __construct(
        RangeProviderInterface $rangeProvider,
        StockStatusSource $stockStatusSource,
        ?AdditionalColumnsInterface $additionalColumns = null,
        string $dataScope = '',
        string $tabName = '',
        string $containerName = 'qty_ranges'
    ) {
        $this->stockStatusSource = $stockStatusSource;
        $this->containerName = $containerName;
        $this->tabName = $tabName;
        $this->rangeProvider = $rangeProvider;
        $this->dataScope = $dataScope;
        $this->additionalColumns = $additionalColumns;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        foreach ($data as $ruleId => $ruleData) {
            foreach ($this->rangeProvider->execute((int) $ruleId) as $range) {
                if ($this->dataScope) {
                    $data[$ruleId]['rule'][$this->dataScope][static::GRID_RANGES][] = $range->getData();
                } else {
                    $data[$ruleId]['rule'][static::GRID_RANGES][] = $range->getData();
                }
            }
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                $this->tabName => [
                    'children' => [
                        $this->containerName => [
                            'children' => [
                                static::GRID_RANGES => $this->getRuleGridConfig(10),
                                static::BUTTON_ADD => $this->getButtonConfig(15)
                            ]
                        ]
                    ]
                ]
            ]
        );

        return $meta;
    }

    protected function getButtonConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'title' => __('Add New Range Status'),
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'component' => 'Amasty_Stockstatus/js/components/button-visible',
                        'sortOrder' => $sortOrder,
                        'actions' => [
                            [
                                'targetName' => sprintf(
                                    '${ $.ns }.${ $.ns }.%s.%s.%s',
                                    $this->tabName,
                                    $this->containerName,
                                    static::GRID_RANGES
                                ),
                                'actionName' => 'processingAddChild',
                                '__disableTmpl' =>  false
                            ]
                        ]
                    ]
                ],
            ]
        ];
    }

    protected function getRuleGridConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add New Range Status'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Amasty_Stockstatus/js/components/dynamic-rows-ranges',
                        'template' => 'ui/dynamic-rows/templates/default',
                        'additionalClasses' => 'admin__field-wide',
                        'dataScope' => $this->dataScope,
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => true,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' =>  false
                        ],
                        'dataProvider' => '${ $.provider}',
                        'dndConfig' => ['enabled' => false]
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('Add New Range Status'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true
                            ],
                        ],
                    ],
                    'children' => $this->getGridColumns()
                ]
            ]
        ];
    }

    private function getGridColumns(): array
    {
        $basicColumns = [
            static::FIELD_FROM => $this->getRangeFieldConfig([
                'sortOrder' => 10,
                'label' => __('Quantity From')
            ]),
            static::FIELD_TO => $this->getRangeFieldConfig([
                'sortOrder' => 20,
                'label' => __('Quantity To')
            ]),
            static::FIELD_STOCK_STATUS => $this->getStatusFieldConfig(30),
            static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(997),
            static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(998),
            static::FIELD_ID => $this->getOptionIdFieldConfig(999),
        ];
        $additionalColumns = $this->additionalColumns ? $this->additionalColumns->execute() : [];

        return $basicColumns + $additionalColumns;
    }

    protected function getPositionFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Hidden::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getOptionIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Hidden::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false
                    ],
                ],
            ],
        ];
    }

    protected function getRangeFieldConfig(array $config): array
    {
        return array_merge_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataType' => Number::NAME,
                            'component' => 'Amasty_Stockstatus/js/components/range-input',
                            'visible' => true,
                            'validation' => [
                                'required-entry' => true,
                                'validate-number' => true,
                                'greater-than-equals-to' => -2147483648,
                                'less-than-equals-to' => 2147483647
                            ],
                            'listens' => [
                                'value' => 'validateAllRanges'
                            ],
                            'rangesComponentName' => '${ $.parentName.replace(/ranges(.\d+)/, \'ranges\') }',
                            '__disableTmpl' =>  false
                        ]
                    ]
                ]
            ],
            [
                'arguments' => [
                    'data' => [
                        'config' => $config
                    ]
                ]
            ]
        );
    }

    protected function getStatusFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Select::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Amasty_Stockstatus/js/components/select',
                        'dataScope' => static::FIELD_STOCK_STATUS,
                        'sortOrder' => $sortOrder,
                        'visible' => true,
                        'label' => __('Custom Stock Status'),
                        'validation' => [
                            'required-entry' => true,
                            'amasty-stockstatus-unique-range' => true
                        ],
                        'validationParams' => '${ $.parentName }',
                        '__disableTmpl' =>  false,
                        'options' => $this->stockStatusSource->toOptionArray()
                    ]
                ]
            ]
        ];
    }

    protected function getIsDeleteFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }
}
