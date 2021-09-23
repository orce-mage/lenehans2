<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Form;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterfaceFactory;
use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Amasty\ImportCore\Api\Config\Profile\FieldInterfaceFactory;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;

class FieldsAdvanced implements FormInterface
{
    /**
     * @var Factory
     */
    private $configClassFactory;

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
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var EntitiesConfigInterfaceFactory
     */
    private $entitiesConfigInterfaceFactory;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        Factory $configClassFactory,
        Repository $assetRepo,
        FieldInterfaceFactory $fieldFactory,
        EntitiesConfigInterfaceFactory $entitiesConfigInterfaceFactory
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->assetRepo = $assetRepo;
        $this->fieldFactory = $fieldFactory;
        $this->entitiesConfigInterfaceFactory = $entitiesConfigInterfaceFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'fieldsConfigAdvanced' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label'])
                                ? __($arguments['label'])
                                : __('Fields Configuration')),
                            'component' => 'Amasty_ImportCore/js/form/components/fieldset',
                            'componentType' => 'fieldset',
                            'dataScope' => 'fields',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        $result['fieldsConfigAdvanced']['children'] = $this->prepareFieldsContainers(
            $entityConfig,
            $this->relationConfigProvider->get($entityConfig->getEntityCode()),
            0
        );

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $result = [];
        if ($profileConfig->getEntitiesConfig()) {
            $result = $this->getFieldsData(
                $profileConfig->getEntityCode(),
                $profileConfig->getEntitiesConfig(),
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }
        if (empty($result)) {
            $result[$profileConfig->getEntityCode()]['fields'] = $this->getEntityFieldsConfig(
                $this->entityConfigProvider->get($profileConfig->getEntityCode())
            );
        }

        return ['fields' => $result];
    }

    public function getFieldsData(string $entityCode, EntitiesConfigInterface $entityConfig, ?array $relationsConfig)
    {
        $result = [];
        $result[$entityCode]['enabled'] = '1';
        $result[$entityCode]['field_code_input'] = $entityConfig->getMap();

        if (!empty($entityConfig->getFields())) {
            foreach ($entityConfig->getFields() as $field) {
                $result[$entityCode]['fields'][] = [
                    'code' => $field->getName(),
                    'file_field' => $field->getMap(),
                    'output_value' => $field->getValue()
                ];
            }
        }
        if (!empty($entityConfig->getSubEntitiesConfig()) && !empty($relationsConfig)) {
            foreach ($relationsConfig as $relation) {
                $currentSubFieldsConfig = null;
                foreach ($entityConfig->getSubEntitiesConfig() as $subFieldsConfig) {
                    if ($subFieldsConfig->getEntityCode() == $relation->getChildEntityCode()) {
                        $currentSubFieldsConfig = $subFieldsConfig;
                        break;
                    }
                }
                if ($currentSubFieldsConfig) {
                    $result[$entityCode] += $this->getFieldsData(
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
        $entityConfig = $this->entitiesConfigInterfaceFactory->create();
        if (!empty($requestFields = $request->getParam('fields'))) {
            $this->prepareProfileFields(
                $profileConfig->getEntityCode(),
                $entityConfig,
                $requestFields,
                $this->relationConfigProvider->get($profileConfig->getEntityCode()),
                $profileConfig->getBehavior()
            );
        }
        $profileConfig->setEntitiesConfig($entityConfig);

        return $this;
    }

    public function prepareProfileFields(
        $entityCode,
        EntitiesConfigInterface $entityConfig,
        array $data,
        ?array $relationsConfig,
        string $behavior
    ) {
        $entityConfig->setEntityCode($entityCode);
        $entityConfig->setBehavior($behavior);
        $entityConfig->setMap($data[$entityCode]['field_code_input'] ?? '');

        $fields = [];
        if (!empty($data[$entityCode]['fields'])) {
            foreach ($data[$entityCode]['fields'] as $requestField) {
                if (empty($requestField['code'])) {
                    continue;
                }
                $field = $this->fieldFactory->create();
                $field->setName($requestField['code']);
                $field->setMap($requestField['file_field'] ?? '');
                $field->setValue($requestField['output_value'] ?? '');
                $fields[$field->getName()] = $field;
            }
        }

        if (!empty($fields)) {
            $entityConfig->setFields($fields);
        }

        if (!empty($relationsConfig)) {
            $subEntitiesConfig = [];
            foreach ($relationsConfig as $relation) {
                if (!empty($data[$entityCode][$relation->getChildEntityCode()])
                    && $data[$entityCode][$relation->getChildEntityCode()]['enabled']) {
                    $subEntityConfig = $this->entitiesConfigInterfaceFactory->create();
                    $this->prepareProfileFields(
                        $relation->getChildEntityCode(),
                        $subEntityConfig,
                        $data[$entityCode],
                        $relation->getRelations(),
                        $behavior
                    );
                    $subEntitiesConfig[] = $subEntityConfig;
                }
            }
            $entityConfig->setSubEntitiesConfig($subEntitiesConfig);
        }
    }

    public function prepareFieldsContainers(
        EntityConfigInterface $entityConfig,
        ?array $relationsConfig,
        int $level,
        $fieldName = ''
    ): array {
        $result = [
            $entityConfig->getEntityCode() => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => $entityConfig->getName(),
                            'dataScope' => $entityConfig->getEntityCode(),
                            'componentType' => 'fieldset',
                            'additionalClasses' => 'amimportcore-fieldset-container',
                            'visible' => true,
                            'collapsible' => true,
                            'opened' => (bool)($level === 0)
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        if ($level === 0) {
            $parent = &$result[$entityConfig->getEntityCode()]['children'];
            $parent['field_code_input'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'input',
                            'component' => 'Magento_Ui/js/form/element/abstract',
                            'componentType' => 'field',
                            'dataType' => 'text',
                            'label' => __('Custom Entity Key'),
                            'tooltipTpl' => 'Amasty_ImportCore/form/element/tooltip',
                            'tooltip' => [
                                'description' => '<img src="'
                                    . $this->assetRepo->getUrl(
                                        'Amasty_ImportCore::images/custom_prefix_tag_name.gif'
                                    )
                                    . '"/>'
                            ],
                        ]
                    ]
                ]
            ];
        } else {
            $result[$entityConfig->getEntityCode()]['children'] = [
                $entityConfig->getEntityCode() . '.enabled' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Enabled'),
                                'dataType' => 'boolean',
                                'prefer' => 'toggle',
                                'valueMap' => ['true' => '1', 'false' => '0'],
                                'default' => 0,
                                'dataScope' => 'enabled',
                                'formElement' => 'checkbox',
                                'visible' => true,
                                'componentType' => 'field',
                                'switcherConfig' => [
                                    'enabled' => true,
                                    'rules'   => [
                                        [
                                            'value'   => 0,
                                            'actions' => [
                                                [
                                                    'target'   => 'index = '
                                                        . $entityConfig->getEntityCode() . '_container',
                                                    'callback' => 'visible',
                                                    'params'   => [false]
                                                ],
                                                [
                                                    'target'   => 'index = filter_container.'
                                                        . $entityConfig->getEntityCode(),
                                                    'callback' => 'visible',
                                                    'params'   => [false]
                                                ]
                                            ]
                                        ],
                                        [
                                            'value'   => 1,
                                            'actions' => [
                                                [
                                                    'target'   => 'index = '
                                                        . $entityConfig->getEntityCode() . '_container',
                                                    'callback' => 'visible',
                                                    'params'   => [true]
                                                ],
                                                [
                                                    'target'   => 'index = filter_container.'
                                                        . $entityConfig->getEntityCode(),
                                                    'callback' => 'visible',
                                                    'params'   => [true]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                $entityConfig->getEntityCode() . '_container' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => '',
                                'dataScope' => '',
                                'componentType' => 'fieldset',
                                'visible' => true
                            ]
                        ]
                    ],
                    'children' => [
                        'field_code' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'input',
                                        'component' => 'Magento_Ui/js/form/element/abstract',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'disabled' => true,
                                        'value' => $fieldName,
                                        'label' => __('Entity Key'),
                                        'tooltip' => [
                                            'description' => __(
                                                'An additional name that is placed before the column name.'
                                            )
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'field_code_input' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'input',
                                        'additionalClasses' => 'amimportcore-field',
                                        'component' => 'Magento_Ui/js/form/element/abstract',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'label' => __('Custom Entity Key'),
                                        'placeholder' => $fieldName
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $parent = &$result[$entityConfig->getEntityCode()]['children']
                       [$entityConfig->getEntityCode() . '_container']['children'];
        }

        $parent[$entityConfig->getEntityCode() . '_selectFieldsModal'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/modal/modal-component',
                        'options' => [
                            'type' => 'slide',
                            'title' => __('Map %1 Fields', $entityConfig->getName()),
                            'modalClass' => 'amimportcore-modal-fields',
                            'buttons' => [
                                [
                                    'text' => __('Map Selected Fields'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => '${ $.name }.selectFields',
                                            '__disableTmpl' => ['targetName' => false],
                                            'actionName' => 'addSelectedFields'
                                        ],
                                        'closeModal'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $parent[$entityConfig->getEntityCode() . '_selectFieldsModal']['children']['selectFields'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component' => 'Amasty_ImportCore/js/fields/select-fields',
                        'template' => 'Amasty_ImportCore/fields/select-fields',
                        'fields' => $this->getEntityFieldsConfig($entityConfig),
                    ]
                ]
            ]
        ];

        $parent['addField'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amimportcore-button -margin',
                        'component' => 'Amasty_ImportCore/js/form/components/button',
                        'title' => __('Map Fields'),
                        'componentType' => 'container',
                        'actions' => [
                            [
                                'targetName' => 'index = ' . $entityConfig->getEntityCode()
                                    . '_selectFieldsModal', 'actionName' => 'toggleModal'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $parent['deleteField'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amimportcore-button -margin -primary',
                        'component' => 'Amasty_ImportCore/js/form/components/button',
                        'title' => __('Delete Table'),
                        'componentType' => 'container',
                        'dynamicVisible' => true,
                        'actions' => [
                            [
                                'targetName' => '${ $.parentName }.fields',
                                '__disableTmpl' => ['targetName' => false],
                                'actionName' => 'removeAllItems'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $parent['fields'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Amasty_ImportCore/js/fields/checked-fields',
                        'template' => 'Amasty_ImportCore/fields/fields',
                        'dataScope' => 'fields',
                        'componentType' => 'container',
                        'deleteBtnPath' => '${ $.parentName }.deleteField',
                        'selectFieldsPath' => '${ $.parentName }' . '.' . $entityConfig->getEntityCode()
                            . '_selectFieldsModal.selectFields',
                    ]
                ]
            ]
        ];
        if (!empty($relationsConfig)) {
            foreach ($relationsConfig as $relation) {
                if ($level) {
                    $result[$entityConfig->getEntityCode()]['children'][$entityConfig->getEntityCode() . '.enabled']
                    ['arguments']['data']['config']['switcherConfig']['rules'][0]['actions'][] = [
                        'target'   => 'index = '
                            . $relation->getChildEntityCode() . '.enabled',
                        'callback' => 'value',
                        'params'   => [0]
                    ];
                }
                $parent += $this->prepareFieldsContainers(
                    $this->entityConfigProvider->get($relation->getChildEntityCode()),
                    $relation->getRelations(),
                    $level + 1,
                    $relation->getSubEntityFieldName()
                );
            }
        }

        return $result;
    }

    public function getEntityFieldsConfig(EntityConfigInterface $entityConfig): array
    {
        $result = [];
        /** @var FieldInterface $fieldConfig */
        foreach ($entityConfig->getFieldsConfig()->getFields() as $fieldConfig) {
            $result[] = [
                'label' => $fieldConfig->getLabel(),
                'name' => $fieldConfig->getMap() ?: $fieldConfig->getName(),
                'code' => $fieldConfig->getName(),
                'value' => $fieldConfig->getValue()
            ];
        }

        return $result;
    }
}
