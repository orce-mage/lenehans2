<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form;

use Amasty\Base\Model\MagentoVersion;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface;
use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldInterface as ProfileFieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterfaceFactory;
use Amasty\ExportCore\Api\Config\Profile\ModifierInterfaceFactory;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Api\FormInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Amasty\ExportCore\Export\Utils\Hash;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;

class FieldsAdvanced implements FormInterface
{
    /**
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var FieldsConfigInterfaceFactory
     */
    private $fieldsConfigFactory;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var ModifierProvider
     */
    private $modifierProvider;

    /**
     * @var ModifierInterfaceFactory
     */
    private $modifierFactory;

    public function __construct(
        FieldsConfigInterfaceFactory $fieldsConfigFactory,
        FieldInterfaceFactory $fieldFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        Repository $assetRepo,
        MagentoVersion $magentoVersion,
        Hash $hash,
        ConfigClassInterfaceFactory $configClassFactory,
        Factory $factory,
        ModifierProvider $modifierProvider,
        ModifierInterfaceFactory $modifierFactory
    ) {
        $this->fieldFactory = $fieldFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->fieldsConfigFactory = $fieldsConfigFactory;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->assetRepo = $assetRepo;
        $this->magentoVersion = $magentoVersion;
        $this->hash = $hash;
        $this->configClassFactory = $configClassFactory;
        $this->factory = $factory;
        $this->modifierProvider = $modifierProvider;
        $this->modifierFactory = $modifierFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $this->arguments = $arguments;
        $result = [
            'fieldsConfigAdvanced' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label'])
                                ? __($arguments['label'])
                                : __('Fields Configuration')),
                            'component' => 'Amasty_ExportCore/js/form/components/fieldset',
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
        if ($profileConfig->getFieldsConfig()) {
            $result = $this->getFieldsData(
                $profileConfig->getEntityCode(),
                $profileConfig->getFieldsConfig(),
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

    public function getFieldsData(
        string $entityCode,
        FieldsConfigInterface $fieldsConfig,
        ?array $relationsConfig
    ): array {
        $result = [];
        $entityConfig = $this->entityConfigProvider->get($entityCode);
        $result[$entityCode]['field_code'] = $fieldsConfig->getName();
        $result[$entityCode]['enabled'] = '1';
        $result[$entityCode]['field_code_output'] = $fieldsConfig->getMap();
        if (!empty($fieldsConfig->getFields())) {
            foreach ($fieldsConfig->getFields() as $field) {
                switch ($field->getType()) {
                    case ProfileFieldInterface::FIELD_TYPE:
                        $modifierOptions =
                            $this->modifierProvider->getAllModifiersByGroups($entityConfig, $field->getName());
                        $result[$entityCode]['fields'][] = [
                            'code' => $field->getName(),
                            'output_value' => $field->getMap(),
                            'modifier' => $this->getSelectedModifiers($field),
                            'options' => $modifierOptions,
                            'sortOrder' => $field->getSortOrder()
                        ];
                        break;
                    case ProfileFieldInterface::STATIC_TYPE:
                        $result[$entityCode]['static_fields'][] = [
                            'code' => $field->getName(),
                            'output_value' => $field->getExtensionAttributes()->getValue()
                        ];
                        break;
                }
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

    protected function getSelectedModifiers(ProfileFieldInterface $field): array
    {
        $selectedModifiersData = [];
        foreach ($field->getModifiers() as $modifier) {
            $class = $this->configClassFactory->create([
                'baseType'  => FieldModifierInterface::class,
                'name'      => $modifier->getModifierClass(),
                'arguments' => []
            ]);

            $selectedModifiersData[] = $this->factory->createObject($class)->getValue($modifier);
        }

        return $selectedModifiersData;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $fieldsConfig = $this->fieldsConfigFactory->create();
        if (!empty($requestFields = $request->getParam('fields'))) {
            $this->prepareProfileFields(
                $profileConfig->getEntityCode(),
                $fieldsConfig,
                $requestFields,
                $this->relationConfigProvider->get($profileConfig->getEntityCode())
            );
        }
        $profileConfig->setFieldsConfig($fieldsConfig);

        return $this;
    }

    public function prepareProfileFields(
        $entityCode,
        FieldsConfigInterface $fieldsConfig,
        array $data,
        ?array $relationsConfig
    ) {
        $fieldsConfig->setName($data[$entityCode]['field_code'] ?? '');
        $fieldsConfig->setMap($data[$entityCode]['field_code_output'] ?? '');
        $fields = [];
        if (!empty($data[$entityCode]['fields'])) {
            $data[$entityCode]['fields'] = array_filter($data[$entityCode]['fields']);
            usort($data[$entityCode]['fields'], function ($a, $b) {
                if (isset($a['sortOrder']) && isset($b['sortOrder'])) {
                    return $a['sortOrder'] < $b['sortOrder'] ? -1 : 1;
                }

                return 0;
            });

            foreach ($data[$entityCode]['fields'] as $requestField) {
                if (empty($requestField['code'])) {
                    continue;
                }
                $field = $this->fieldFactory->create();
                $field->setName($requestField['code']);
                $field->setMap($requestField['output_value'] ?? '');
                $field->setType($this->getFieldType($field->getName(), $entityCode));
                $field->setModifiers($this->getModifiers($requestField['modifier'] ?? [], $field));
                $field->setSortOrder($requestField['sortOrder'] ?? null);

                $fields[$field->getName()] = $field;
            }
        }

        if (!empty($data[$entityCode]['static_fields'])) {
            foreach ($data[$entityCode]['static_fields'] as $requestField) {
                if (empty($requestField['code'])) {
                    continue;
                }
                $field = $this->fieldFactory->create();
                $field->setName($requestField['code']);
                $field->getExtensionAttributes()->setValue($requestField['output_value'] ?? '');
                $field->setType(ProfileFieldInterface::STATIC_TYPE);
                $fields[$field->getName()] = $field;
            }
        }

        if (!empty($fields)) {
            $fieldsConfig->setFields($fields);
        }

        if (!empty($relationsConfig)) {
            $subentitiesFieldsConfig = [];
            foreach ($relationsConfig as $relation) {
                if (!empty($data[$entityCode][$relation->getChildEntityCode()])
                    && $data[$entityCode][$relation->getChildEntityCode()]['enabled']) {
                    $subentityFieldsConfig = $this->fieldsConfigFactory->create();
                    $this->prepareProfileFields(
                        $relation->getChildEntityCode(),
                        $subentityFieldsConfig,
                        $data[$entityCode],
                        $relation->getRelations()
                    );
                    $subentitiesFieldsConfig[] = $subentityFieldsConfig;
                }
            }
            $fieldsConfig->setSubEntitiesFieldsConfig($subentitiesFieldsConfig);
        }
    }

    protected function getModifiers(array $modifiersData, ProfileFieldInterface $field): array
    {
        $modifiers = [];
        if (empty($modifiersData) || !is_array($modifiersData)) {
            return $modifiers;
        }
        foreach ($modifiersData as $modifierData) {
            if (empty($modifierData['select_value'])) {
                continue;
            }
            $modifier = $this->modifierFactory->create();
            $modifier->setModifierClass((string)$modifierData['select_value']);
            $class = $this->configClassFactory->create([
                'baseType'  => FieldModifierInterface::class,
                'name'      => $modifier->getModifierClass(),
                'arguments' => []
            ]);

            $arguments = $this->factory->createObject($class)->prepareArguments($field, $modifierData);
            $modifier->setArguments($arguments);

            $modifiers[] = $modifier;
        }

        return $modifiers;
    }

    public function getFieldType($field, $entityCode): string
    {
        $entity = $this->entityConfigProvider->get($entityCode);
        if (!empty($entity->getFieldsConfig()->getVirtualFields())) {
            foreach ($entity->getFieldsConfig()->getVirtualFields() as $fieldConfig) {
                if ($fieldConfig->getName() === $field) {
                    return ProfileFieldInterface::VIRTUAL_TYPE;
                }
            }
        }

        return ProfileFieldInterface::FIELD_TYPE;
    }

    public function prepareFieldsContainers(
        EntityConfigInterface $entityConfig,
        ?array $relationsConfig,
        int $level,
        string $fieldName = '',
        string $parentKey = ''
    ): array {
        $index = $this->getEntityIndex($parentKey, $entityConfig->getEntityCode());
        $result = [
            $index => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => $entityConfig->getName(),
                            'dataScope' => $entityConfig->getEntityCode(),
                            'componentType' => 'fieldset',
                            'additionalClasses' => 'amexportcore-fieldset-container',
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
            $parent = &$result[$index]['children'];
            $parent['field_code_output'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'input',
                            'component' => 'Magento_Ui/js/form/element/abstract',
                            'componentType' => 'field',
                            'dataType' => 'text',
                            'label' => __('Custom Entity Key'),
                            'tooltipTpl' => 'Amasty_ExportCore/form/element/tooltip',
                            'tooltip' => [
                                'description' => '<img src="'
                                    . $this->assetRepo->getUrl(
                                        'Amasty_ExportCore::images/custom_prefix_tag_name.gif'
                                    )
                                    . '"/>'
                            ],
                        ]
                    ]
                ]
            ];
        } else {
            $result[$index]['children'] = [
                $index . '.enabled' => [
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
                                                    'target'   => 'index = ' . $index . '_container',
                                                    'callback' => 'visible',
                                                    'params'   => [false]
                                                ],
                                                [
                                                    'target'   => 'index = filter_container.' . $index,
                                                    'callback' => 'visible',
                                                    'params'   => [false]
                                                ]
                                            ]
                                        ],
                                        [
                                            'value'   => 1,
                                            'actions' => [
                                                [
                                                    'target'   => 'index = ' . $index . '_container',
                                                    'callback' => 'visible',
                                                    'params'   => [true]
                                                ],
                                                [
                                                    'target'   => 'index = filter_container.' . $index,
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
                $index . '_container' => [
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
                        'field_code_output' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => 'input',
                                        'component' => 'Magento_Ui/js/form/element/abstract',
                                        'componentType' => 'field',
                                        'dataType' => 'text',
                                        'label' => __('Output Entity Key'),
                                        'tooltip' => [
                                            'description' => __(
                                                'Replace the name specified in the setting \'Entity Key\'.'
                                            )
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            $parent = &$result[$index]['children']
                       [$index . '_container']['children'];
        }

        $parent[$index . '_selectFieldsModal'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/modal/modal-component',
                        'options' => [
                            'type' => 'slide',
                            'title' => __('Add %1 Fields', $entityConfig->getName()),
                            'modalClass' => 'amexportcore-modal-fields',
                            'buttons' => [
                                [
                                    'text' => __('Add Selected Fields'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => '${ $.name }.selectFields',
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

        if ($this->isDisableTmplAllowed()) {
            $parent[$index . '_selectFieldsModal']['arguments']['data']['config']
                ['options']['buttons'][0]['actions'][0]['__disableTmpl'] = ['targetName' => false];
        }

        $parent[$index . '_selectFieldsModal']['children']['selectFields'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'container',
                        'component' => 'Amasty_ExportCore/js/fields/select-fields',
                        'template' => 'Amasty_ExportCore/fields/select-fields',
                        'fields' => $this->getEntityFieldsConfig($entityConfig),
                    ]
                ]
            ]
        ];

        $parent['addField'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amexportcore-button -light amexportcore-button-margin',
                        'component' => 'Amasty_ExportCore/js/form/components/button',
                        'title' => __('Add Fields'),
                        'componentType' => 'container',
                        'actions' => [
                            [
                                'targetName' => 'index = ' . $index . '_selectFieldsModal',
                                'actionName' => 'toggleModal'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $modifierConfig = [];
        foreach ($this->modifierProvider->getAllModifiers($entityConfig) as $modifier) {
            $class = $this->configClassFactory->create([
                'baseType'  => FieldModifierInterface::class,
                'name'      => $modifier,
                'arguments' => []
            ]);

            $modifierConfig[$modifier] = $this->factory->createObject($class)->getJsConfig();
        }

        $parent['deleteField'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'buttonClasses' => 'amexportcore-button -light amexportcore-button-margin',
                        'component' => 'Amasty_ExportCore/js/form/components/button',
                        'title' => __('Delete Table'),
                        'componentType' => 'container',
                        'dynamicVisible' => true,
                        'actions' => [
                            [
                                'targetName' => '${ $.parentName }.fields',
                                'actionName' => 'removeAllItems'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($this->isDisableTmplAllowed()) {
            $parent['deleteField']['arguments']['data']['config']['actions'][0]['__disableTmpl'] =
                ['targetName' => false];
        }

        $parent['fields'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Amasty_ExportCore/js/fields/checked-fields',
                        'template' => 'Amasty_ExportCore/fields/fields',
                        'dataScope' => 'fields',
                        'modifierConfig' => $modifierConfig,
                        'componentType' => 'container',
                        'deleteBtnPath' => '${ $.parentName }.deleteField',
                        'selectFieldsPath' => '${ $.parentName }' . '.' . $index . '_selectFieldsModal.selectFields',
                    ]
                ]
            ]
        ];
        if (!empty($relationsConfig)) {
            foreach ($relationsConfig as $relation) {
                if ($level) {
                    $childIndex = $this->hash->hash($index . $relation->getChildEntityCode());
                    $result[$index]['children'][$index . '.enabled']
                    ['arguments']['data']['config']['switcherConfig']['rules'][0]['actions'][] = [
                        'target'   => 'index = ' . $childIndex . '.enabled',
                        'callback' => 'value',
                        'params'   => [0]
                    ];
                }
                $parent += $this->prepareFieldsContainers(
                    $this->entityConfigProvider->get($relation->getChildEntityCode()),
                    $relation->getRelations(),
                    $level + 1,
                    $relation->getSubEntityFieldName(),
                    $index
                );
            }
        }

        return $result;
    }

    protected function getEntityIndex(string $parentKey, string $entityKey): string
    {
        return $this->hash->hash($parentKey . $entityKey);
    }

    public function getEntityFieldsConfig(EntityConfigInterface $entityConfig): array
    {
        $result = [];
        $mergedFields = array_merge(
            $entityConfig->getFieldsConfig()->getFields(),
            $entityConfig->getFieldsConfig()->getVirtualFields()
        );

        /** @var FieldInterface|VirtualFieldInterface $fieldConfig */
        foreach ($mergedFields as $fieldConfig) {
            $modifierOptions = $this->modifierProvider->getAllModifiersByGroups($entityConfig, $fieldConfig->getName());
            $result[] = [
                'label' => $fieldConfig->getLabel(),
                'name' => $fieldConfig->getName(),
                'output_value' => $fieldConfig->getMap(),
                'code' => $fieldConfig->getName(),
                'modifier' => $this->modifierProvider->getEntityFieldModifiersValue(
                    $entityConfig,
                    $fieldConfig->getName()
                ),
                'options' => $modifierOptions
            ];
        }

        return $result;
    }

    private function isDisableTmplAllowed(): bool
    {
        return version_compare($this->magentoVersion->get(), '2.4.0', '>=');
    }
}
