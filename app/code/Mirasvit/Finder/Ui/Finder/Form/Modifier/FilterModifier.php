<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Ui\Finder\Form\Modifier;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Ui\Finder\Source\FilterAttributeSource;
use Mirasvit\Finder\Ui\Finder\Source\FilterDisplayModeSource;
use Mirasvit\Finder\Ui\Finder\Source\FilterLinkTypeSource;
use Mirasvit\Finder\Ui\Finder\Source\FilterSortModeSource;

class FilterModifier implements ModifierInterface
{
    private $displayModeSource;

    private $sortModeSource;

    private $linkTypeSource;

    private $attributeSource;

    public function __construct(
        FilterDisplayModeSource $displayModeSource,
        FilterSortModeSource $sortModeSource,
        FilterLinkTypeSource $linkTypeSource,
        FilterAttributeSource $attributeSource
    ) {
        $this->displayModeSource = $displayModeSource;
        $this->sortModeSource    = $sortModeSource;
        $this->linkTypeSource    = $linkTypeSource;
        $this->attributeSource   = $attributeSource;
    }

    public function modifyMeta(array $meta): array
    {
        return [
            FilterInterface::POSITION                => $this->getFieldPositionConfig(0),
            FiltersModifier::FILTER_CONTAINER_COMMON => $this->getContainerCommonConfig(10),
            FiltersModifier::FILTER_CONTAINER_EXTRA  => $this->getContainerExtraConfig(20),
        ];
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    private function getFieldPositionConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Hidden::NAME,
                        'dataScope'     => FilterInterface::POSITION,
                        'dataType'      => Number::NAME,
                        'visible'       => false,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    private function getContainerCommonConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Container::NAME,
                        'formElement'       => Container::NAME,
                        'component'         => 'Magento_Ui/js/form/components/group',
                        'breakLine'         => false,
                        'showLabel'         => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder'         => $sortOrder,
                    ],
                ],
            ],
            'children'  => [
                FilterInterface::ID             => $this->getOptionIdFieldConfig(20),
                FilterInterface::NAME           => $this->getFieldNameConfig(
                    20,
                    [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label'       => __('Filter Name'),
                                    'component'   => 'Magento_Catalog/component/static-type-input',
                                    'valueUpdate' => 'input',
                                    'imports'     => [
                                        'optionId'      => '${ $.provider }:${ $.parentScope }.option_id',
                                        'isUseDefault'  => '${ $.provider }:${ $.parentScope }.is_use_default',
                                        '__disableTmpl' => ['optionId' => false, 'isUseDefault' => false],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ),
                FilterInterface::LINK_TYPE      => $this->getFieldLinkTypeConfig(30),
                FilterInterface::ATTRIBUTE_CODE => $this->getFieldAttributeConfig(40),
            ],
        ];
    }

    private function getContainerExtraConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'     => Container::NAME,
                        'formElement'       => Container::NAME,
                        'component'         => 'Magento_Ui/js/form/components/group',
                        'breakLine'         => false,
                        'showLabel'         => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder'         => $sortOrder,
                    ],
                ],
            ],
            'children'  => [
                FilterInterface::DISPLAY_MODE   => $this->getFieldDisplayModeConfig(30),
                FilterInterface::SORT_MODE      => $this->getFieldSortModeConfig(35),
                FilterInterface::IS_REQUIRED    => $this->getFieldIsRequiredConfig(40),
                FilterInterface::IS_MULTISELECT => $this->getFieldIsMultiselectConfig(50),
            ],
        ];
    }

    private function getOptionIdFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope'     => FilterInterface::ID,
                        'sortOrder'     => $sortOrder,
                        'visible'       => false,
                    ],
                ],
            ],
        ];
    }

    private function getFieldNameConfig(int $sortOrder, array $options = []): array
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => __('Title'),
                            'componentType' => Field::NAME,
                            'formElement'   => Input::NAME,
                            'dataScope'     => FilterInterface::NAME,
                            'dataType'      => Text::NAME,
                            'sortOrder'     => $sortOrder,
                            'validation'    => [
                                'required-entry' => true,
                            ],
                        ],
                    ],
                ],
            ],
            $options
        );
    }

    private function getFieldLinkTypeConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Link To'),
                        'componentType' => Field::NAME,
                        'formElement'   => Select::NAME,
                        'component'     => 'Mirasvit_Finder/js/finder/form/filter/linkType',
                        'elementTmpl'   => 'ui/grid/filters/elements/ui-select',
                        'dataScope'     => FilterInterface::LINK_TYPE,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'options'       => $this->linkTypeSource->toOptionArray(),
                        'disableLabel'  => true,
                        'multiple'      => false,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                        'value'         => FilterInterface::LINK_TYPE_CUSTOM,
                        'groupsConfig'  => [
                            FilterInterface::LINK_TYPE_ATTRIBUTE => [
                                'values'  => [FilterInterface::LINK_TYPE_ATTRIBUTE],
                                'indexes' => [
                                    FilterInterface::ATTRIBUTE_CODE,
                                ],
                            ],
                            FilterInterface::LINK_TYPE_CUSTOM    => [
                                'values'  => [FilterInterface::LINK_TYPE_CUSTOM],
                                'indexes' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getFieldAttributeConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Attribute'),
                        'componentType' => Field::NAME,
                        'formElement'   => Select::NAME,
                        'component'     => 'Magento_Ui/js/form/element/ui-select',
                        'elementTmpl'   => 'ui/grid/filters/elements/ui-select',
                        'selectType'    => 'optgroup',
                        'dataScope'     => FilterInterface::ATTRIBUTE_CODE,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'options'       => $this->attributeSource->toOptionArray(),
                        'disableLabel'  => true,
                        'multiple'      => false,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getFieldDisplayModeConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Display Mode'),
                        'componentType' => Field::NAME,
                        'formElement'   => Select::NAME,
                        'component'     => 'Magento_Ui/js/form/element/ui-select',
                        'elementTmpl'   => 'ui/grid/filters/elements/ui-select',
                        'selectType'    => 'optgroup',
                        'dataScope'     => FilterInterface::DISPLAY_MODE,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'options'       => $this->displayModeSource->toOptionArray(),
                        'disableLabel'  => true,
                        'multiple'      => false,
                        'value'         => FilterInterface::DISPLAY_MODE_DROPDOWN,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getFieldSortModeConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Sort Mode'),
                        'componentType' => Field::NAME,
                        'formElement'   => Select::NAME,
                        'component'     => 'Magento_Ui/js/form/element/ui-select',
                        'elementTmpl'   => 'ui/grid/filters/elements/ui-select',
                        'selectType'    => 'optgroup',
                        'dataScope'     => FilterInterface::SORT_MODE,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'options'       => $this->sortModeSource->toOptionArray(),
                        'disableLabel'  => true,
                        'value'         => FilterInterface::SORT_MODE_ASC_STRING,
                        'multiple'      => false,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getFieldIsRequiredConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Required'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => FilterInterface::IS_REQUIRED,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'value'         => '1',
                        'valueMap'      => [
                            'true'  => '1',
                            'false' => '0',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getFieldIsMultiselectConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Multiselect'),
                        'componentType' => Field::NAME,
                        'formElement'   => Checkbox::NAME,
                        'dataScope'     => FilterInterface::IS_MULTISELECT,
                        'dataType'      => Text::NAME,
                        'sortOrder'     => $sortOrder,
                        'value'         => '0',
                        'valueMap'      => [
                            'true'  => '1',
                            'false' => '0',
                        ],
                    ],
                ],
            ],
        ];
    }
}
