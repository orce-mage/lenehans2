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

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Repository\FilterRepository;

class FiltersModifier implements ModifierInterface
{
    const FORM_NAME           = 'mst_finder_finder_form';
    const DATA_SOURCE_DEFAULT = 'mst_finder_finder_form.mst_finder_finder_form_data_source';
    /**#@+
     * Group values
     */
    const GROUP_CUSTOM_OPTIONS_NAME               = 'custom_options';
    const GROUP_CUSTOM_OPTIONS_SCOPE              = '';
    const GROUP_CUSTOM_OPTIONS_PREVIOUS_NAME      = 'search-engine-optimization';
    const GROUP_CUSTOM_OPTIONS_DEFAULT_SORT_ORDER = 31;
    /**#@-*/

    /**#@+
     * Button values
     */
    const BUTTON_ADD = 'button_add';
    /**#@-*/

    /**#@+
     * Container values
     */
    const CONTAINER_HEADER_NAME   = 'container_header';
    const CONTAINER_OPTION        = 'container_option';
    const FILTER_CONTAINER_COMMON = 'container_common';
    const FILTER_CONTAINER_EXTRA  = 'container_extra';
    /**#@-*/

    /**#@+
     * Grid values
     */
    const GRID_OPTIONS_NAME     = FilterInterface::TABLE_NAME;
    const GRID_TYPE_SELECT_NAME = 'values';
    /**#@-*/

    /**#@+
     * Field values
     */
    const FIELD_ENABLE = 'affect_product_custom_options';

    const FIELD_TYPE_NAME           = 'type';
    const FIELD_PRICE_NAME          = 'price';
    const FIELD_PRICE_TYPE_NAME     = 'price_type';
    const FIELD_SKU_NAME            = 'sku';
    const FIELD_MAX_CHARACTERS_NAME = 'max_characters';
    const FIELD_FILE_EXTENSION_NAME = 'file_extension';
    const FIELD_IMAGE_SIZE_X_NAME   = 'image_size_x';
    const FIELD_IMAGE_SIZE_Y_NAME   = 'image_size_y';
    const FIELD_IS_DELETE           = 'is_delete';
    const FIELD_IS_USE_DEFAULT      = 'is_use_default';
    /**#@-*/

    /**#@+
     * Import options values
     */
    const IMPORT_OPTIONS_MODAL   = 'import_options_modal';
    const CUSTOM_OPTIONS_LISTING = 'product_custom_options_listing';

    /**#@-*/

    protected $locator;

    protected $storeManager;


    protected $urlBuilder;

    protected $arrayManager;

    protected $meta = [];

    private   $filterRepository;

    private   $filterModifier;

    public function __construct(
        FilterRepository $filterRepository,
        FilterModifier $filterModifier,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager
    ) {
        $this->filterRepository = $filterRepository;
        $this->filterModifier   = $filterModifier;
        $this->locator          = $locator;
        $this->storeManager     = $storeManager;
        $this->urlBuilder       = $urlBuilder;
        $this->arrayManager     = $arrayManager;
    }

    public function modifyData(array $data): array
    {
        foreach ($data as $finderId => $finderData) {
            $filters = $this->filterRepository->getCollection();
            $filters->addFieldToFilter(FilterInterface::FINDER_ID, $finderId)
                ->setOrder(FilterInterface::POSITION, 'asc');

            $filtersData = [];
            foreach ($filters as $filter) {
                $filterData = [
                    FilterInterface::ID             => $filter->getId(),
                    FilterInterface::NAME           => $filter->getName(),
                    FilterInterface::LINK_TYPE      => $filter->getLinkType(),
                    FilterInterface::ATTRIBUTE_CODE => $filter->getAttributeCode(),
                    FilterInterface::DISPLAY_MODE   => $filter->getDisplayMode(),
                    FilterInterface::SORT_MODE      => $filter->getSortMode(),
                    FilterInterface::IS_MULTISELECT => $filter->isMultiselect() ? '1' : '0',
                    FilterInterface::IS_REQUIRED    => $filter->isRequired() ? '1' : '0',
                ];

                $filtersData[] = $filterData;
            }

            $data[$finderId][self::GRID_OPTIONS_NAME] = $filtersData;
        }

        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        $this->meta = $meta;

        $this->meta = array_replace_recursive(
            $this->meta,
            [
                static::GROUP_CUSTOM_OPTIONS_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'         => __('Filters'),
                                'componentType' => Fieldset::NAME,
                                'dataScope'     => self::GROUP_CUSTOM_OPTIONS_SCOPE,
                                'collapsible'   => true,
                                'opened'        => true,
                                'sortOrder'     => 30,
                            ],
                        ],
                    ],
                    'children'  => [
                        self::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                        self::FIELD_ENABLE          => $this->getEnableFieldConfig(20),
                        self::GRID_OPTIONS_NAME     => $this->getFiltersGridConfig(30),
                    ],
                ],
            ]
        );

        return $this->meta;
    }


    /**
     * Get config for hidden field responsible for enabling custom options processing
     */
    private function getEnableFieldConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope'     => static::FIELD_ENABLE,
                        'dataType'      => Number::NAME,
                        'visible'       => false,
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    private function getFiltersGridConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'       => DynamicRows::NAME,
                        'component'           => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template'            => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses'   => 'admin__field-wide',
                        'deleteProperty'      => self::FIELD_IS_DELETE,
                        'deleteValue'         => '1',
                        'addButton'           => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader'       => false,
                        'collapsibleHeader'   => true,
                        'sortOrder'           => $sortOrder,
                        'dataProvider'        => static::CUSTOM_OPTIONS_LISTING,
                        'imports'             => [
                            'insertData'    => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                    ],
                ],
            ],
            'children'  => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel'      => __('New Filter'),
                                'componentType'    => Container::NAME,
                                'component'        => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_OPTION . '.' . FilterInterface::POSITION,
                                'isTemplate'       => true,
                                'is_collection'    => true,
                            ],
                        ],
                    ],
                    'children'  => [
                        static::CONTAINER_OPTION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible'   => true,
                                        'label'         => null,
                                        'sortOrder'     => 10,
                                        'opened'        => true,
                                    ],
                                ],
                            ],
                            'children'  => $this->filterModifier->modifyMeta([]),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getHeaderContainerConfig(int $sortOrder): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => null,
                        'formElement'   => Container::NAME,
                        'componentType' => Container::NAME,
                        'template'      => 'ui/form/components/complex',
                        'sortOrder'     => $sortOrder,
                        'content'       => '',
                    ],
                ],
            ],
            'children'  => [
                self::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title'         => __('Add Filter'),
                                'formElement'   => Container::NAME,
                                'componentType' => Container::NAME,
                                'component'     => 'Magento_Ui/js/form/components/button',
                                'sortOrder'     => 20,
                                'actions'       => [
                                    [
                                        'targetName'    => 'mst_finder_finder_form.mst_finder_finder_form.custom_options.mst_finder_filter',
                                        '__disableTmpl' => ['targetName' => false],
                                        'actionName'    => 'processingAddChild',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
