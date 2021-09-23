<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterfaceFactory;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ExportCore\Export\Utils\Hash;
use Amasty\ExportCore\Export\Utils\StaticViewFileResolver;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;

class Filter implements \Amasty\ExportCore\Api\FormInterface
{
    /**
     * @var Factory
     */
    private $configClassFactory;

    /**
     * @var FieldFilterInterfaceFactory
     */
    private $filterFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var StaticViewFileResolver
     */
    private $staticViewFileResolver;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var string
     */
    private $excludeParentImage;

    public function __construct(
        FieldFilterInterfaceFactory $filterFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        Factory $configClassFactory,
        Repository $assetRepo,
        StaticViewFileResolver $staticViewFileResolver,
        Hash $hash
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->filterFactory = $filterFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->assetRepo = $assetRepo;
        $this->staticViewFileResolver = $staticViewFileResolver;
        $this->hash = $hash;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $this->arguments = $arguments;
        $result = [
            'filter' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Filter')),
                            'componentType' => 'fieldset',
                            'dataScope' => 'filter',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => []
            ]
        ];
        $result['filter']['children'] = $this->prepareEntitiesFilters(
            $entityConfig,
            $this->relationConfigProvider->get($entityConfig->getEntityCode()),
            0
        );

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $result = [];
        if ($profileConfig->getFieldsConfig()) {
            $result = $this->getFiltersData(
                $profileConfig->getEntityCode(),
                $profileConfig->getFieldsConfig(),
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }
        if (!empty($result)) {
            return ['filter' => $result];
        }

        return [];
    }

    public function getFiltersData(
        string $entityCode,
        FieldsConfigInterface $fieldsConfig,
        ?array $relationsConfig
    ) :array {
        $result = [];
        $result[$entityCode]['exclude_row_if_no_results_found'] =
            $fieldsConfig->isExcludeRowIfNoResultsFound() ? '1' : '0';
        if (!empty($fieldsConfig->getFilters())) {
            $id = 1;
            $entitiesFieldFilters = $this->getEntityFieldFilters($entityCode);
            foreach ($fieldsConfig->getFilters() as $filter) {
                if (!isset($result[$entityCode])) {
                    $result[$entityCode]['filters'] = [];
                }

                $value = $this->configClassFactory
                    ->createObject($entitiesFieldFilters[$filter->getField()]->getMetaClass())
                    ->getValue($filter);
                $result[$entityCode]['filters'][] = [
                    'id' => $id++,
                    'field' => $filter->getField(),
                    'condition' => $filter->getCondition(),
                    'value' => $value
                ];
            }
        }
        if (!empty($fieldsConfig->getSubEntitiesFieldsConfig()) && !empty($relationsConfig)) {
            foreach ($relationsConfig as $relation) {
                $currentSubFieldsConfig = null;
                foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subFieldsConfig) {
                    if ($subFieldsConfig->getName() == $relation->getSubEntityFieldName()) {
                        $currentSubFieldsConfig = $subFieldsConfig;
                        break;
                    }
                }
                if ($currentSubFieldsConfig) {
                    $result[$entityCode] += $this->getFiltersData(
                        $relation->getChildEntityCode(),
                        $currentSubFieldsConfig,
                        $relation->getRelations()
                    );
                }
            }
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if (!empty($requestFields = $request->getParam('filter'))) {
            $this->prepareProfileFilters(
                $profileConfig->getEntityCode(),
                $profileConfig->getFieldsConfig(),
                $requestFields,
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }

        return $this;
    }

    public function prepareProfileFilters(
        $entityCode,
        FieldsConfigInterface $fieldsConfig,
        array $data,
        ?array $relationsConfig
    ) {
        $fieldsConfig->setIsExcludeRowIfNoResultsFound(
            (bool)($data[$entityCode]['exclude_row_if_no_results_found'] ?? false)
        );
        if (!empty($data[$entityCode]['filters'])) {
            $filters = [];
            $entityFieldFilters = $this->getEntityFieldFilters($entityCode);
            foreach ($data[$entityCode]['filters'] as $condition) {
                if (empty($condition['field'])) {
                    continue;
                }

                if (!empty($entityFieldFilters[$condition['field']])) {
                    $filter = $this->filterFactory->create();
                    $filter->setField($condition['field']);
                    $filter->setCondition($condition['condition'] ?? '');
                    $this->configClassFactory
                        ->createObject($entityFieldFilters[$condition['field']]->getMetaClass())
                        ->prepareConfig($filter, $condition['value'] ?? '');

                    $filters[] = $filter;
                }
            }
            $fieldsConfig->setFilters($filters);
        }

        if (!empty($relationsConfig)) {
            foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subentityFieldConfig) {
                $currentRelation = false;
                foreach ($relationsConfig as $relation) {
                    if ($relation->getSubEntityFieldName() == $subentityFieldConfig->getName()) {
                        $currentRelation = $relation;
                        break;
                    }
                }
                if ($currentRelation) {
                    $this->prepareProfileFilters(
                        $currentRelation->getChildEntityCode(),
                        $subentityFieldConfig,
                        $data[$entityCode],
                        $currentRelation->getRelations()
                    );
                }
            }
        }
    }

    public function getEntityFieldFilters(string $entityCode): array
    {
        $entityFields = $this->entityConfigProvider->get($entityCode)
            ->getFieldsConfig()->getFields();
        $entityFieldFilters = [];
        foreach ($entityFields as $field) {
            $entityFieldFilters[$field->getName()] = $field->getFilter();
        }

        return $entityFieldFilters;
    }

    private function prepareEntitiesFilters(
        EntityConfigInterface $entityConfig,
        ?array $relationsConfig,
        int $level = 0,
        string $parentKey = ''
    ): array {
        $index = $this->hash->hash($parentKey . $entityConfig->getEntityCode());
        $result = [
            'filter_container.' . $index => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => $entityConfig->getName(),
                            'dataScope' => $entityConfig->getEntityCode(),
                            'componentType' => 'fieldset',
                            'visible' => true,
                            'collapsible' => true,
                            'opened' => false,
                            'template' => 'Amasty_ExportCore/form/fieldset'
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        if ($level === 0) {
            $parent = &$result['filter_container.' . $index]['children'];
        } else {
            $result['filter_container.' . $index]['children'] = [
                'exclude_row_if_no_results_found' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'         => __('Exclude Parent Entity Row If No Results Found'),
                                'dataType'      => 'boolean',
                                'prefer'        => 'toggle',
                                'dataScope'     => 'exclude_row_if_no_results_found',
                                'valueMap'      => ['true' => '1', 'false' => '0'],
                                'default'       => '0',
                                'formElement'   => 'checkbox',
                                'visible'       => true,
                                'notice'        => __('Enable the setting to exclude parent entity row from'
                                    . ' the export file if the child entity doesn\'t have filter'
                                    . ' results relative to the parent entity.'),
                                'componentType' => 'field',
                                'tooltipTpl' => 'Amasty_ExportCore/form/element/tooltip',
                                'tooltip' => [
                                    'description' => '<img src="' . $this->getExcludeParentImage() . '"/>'
                                ],
                            ]
                        ]
                    ]
                ],
            ];
            $parent = &$result['filter_container.' . $index]['children'];
        }

        $filterConfig = [];
        $fieldsOptions = [];

        $fields = $entityConfig->getFieldsConfig()->getFields();
        foreach ($fields as $field) {
            if ($field->getFilter()) {
                if ($field->getLabel()) {
                    $optionLabel = __($field->getLabel()) . '(' . $field->getMap() ?: $field->getName() . ')';
                } else {
                    $optionLabel = $field->getMap() ?: $field->getName();
                }
                $fieldsOptions[] = ['value' => $field->getName(), 'label' => $optionLabel];

                $metaClass = $this->configClassFactory->createObject($field->getFilter()->getMetaClass());
                $filterConfig[$field->getName()] = [
                    'config' => $metaClass->getJsConfig($field),
                    'conditions' => $metaClass->getConditions($field)
                ];
            }
        }

        $parent['addFilter'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amexportcore-button  amexportcore-button-margin',
                        'component' => 'Amasty_ExportCore/js/form/components/button',
                        'title' => __('Add Filter'),
                        'componentType' => 'container',
                        'actions' => [
                            [
                                'targetName' => 'index = filter.' . $index . '.filters',
                                'actionName' => 'processingAddChild',
                                'params' => [false, false, false]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $parent['filter.' . $index . '.filters'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Amasty_ExportCore/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide amexportcore-dynamic-rows',
                        'componentType' => 'dynamicRows',
                        'recordTemplate' => 'record',
                        'addButton' => false,
                        'columnsHeader' => false,
                        'dataScope' => '',
                        'columnsHeaderAfterRender' => true,
                        'template' => 'ui/dynamic-rows/templates/default',
                        'filterConfig' => $filterConfig,
                        'renderDefaultRecord' => false,
                        'identificationProperty' => 'id',
                        'positionProvider' => 'position',
                        'dndConfig' => [
                            'enabled' => false
                        ]
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'isTemplate' => true,
                                'componentType' => 'container',
                                'dataScope' => 'filters',
                                'positionProvider' => 'position',
                                'is_collection' => true
                            ],
                            'template' => 'templates/container/default',
                        ],
                    ],
                    'children' => [
                        'field' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'select',
                                        'default' => true,
                                        'additionalClasses' => '-amwidth30 amexportcore-filter',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'dataScope' => 'field',
                                        'component' => 'Magento_Ui/js/form/element/ui-select',
                                        'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                        'filterOptions' => true,
                                        'showCheckbox' => false,
                                        'multiple' => false,
                                        'disableLabel' => true,
                                        'label' => 'Field For Filtering',
                                        'sortOrder' => '10',
                                        'options' => $fieldsOptions
                                    ],
                                ],
                            ],
                        ],
                        'condition' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'component' => 'Amasty_ExportCore/js/condition-select',
                                        'elementTmpl' => 'Amasty_ExportCore/form/element/select',
                                        'formElement' => 'select',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'additionalClasses' => '-amwidth30 amexportcore-filter',
                                        'dataScope' => 'condition',
                                        'label' => 'Filter Condition',
                                        'sortOrder' => '20'
                                    ],
                                ],
                            ],
                        ],
                        'value' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'topParentContainer' => $index,
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'component' => 'Amasty_ExportCore/js/condition-value',
                                        'dataScope' => '',
                                        'componentType' => 'container',
                                        'additionalClasses' => '-amwidth30 amexportcore-filter',
                                        'label' => 'Value',
                                        'sortOrder' => '30',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'component' => 'Magento_Ui/js/dynamic-rows/action-delete',
                                        'template' => 'ui/dynamic-rows/cells/action-delete',
                                        'additionalClasses' => 'amwidth40px data-grid-actions-cell amexportcore-remove',
                                        'componentType' => 'actionDelete',
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'dataType' => 'text',
                                        'label' => '',
                                        'sortOrder' => '80',
                                    ],
                                ],
                            ]
                        ],
                        'id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'input',
                                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                        'component' => 'Magento_Ui/js/form/element/text',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'dataScope' => 'id',
                                        'sortOrder' => '999',
                                        'visible' => false
                                    ],
                                ],
                            ],
                        ]
                    ],
                ],
            ]
        ];

        if ($relationsConfig) {
            foreach ($relationsConfig as $relation) {
                $parent += $this->prepareEntitiesFilters(
                    $this->entityConfigProvider->get($relation->getChildEntityCode()),
                    $relation->getRelations(),
                    $level + 1,
                    $index
                );
            }
        }

        return $result;
    }

    protected function getExcludeParentImage(): string
    {
        if (null === $this->excludeParentImage) {
            if (!empty($this->arguments['excludeParentImage'])) {
                $this->excludeParentImage = $this->assetRepo->getUrl($this->arguments['excludeParentImage']);
            } else {
                $this->excludeParentImage = $this->assetRepo->getUrl(
                    $this->staticViewFileResolver->getFileId(
                        'images/exclude_parent_entity_row_if_no_results_found.gif'
                    )
                );
            }
        }

        return $this->excludeParentImage;
    }
}
