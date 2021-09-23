<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Form;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Amasty\ImportCore\Import\OptionSource\ValidationStrategy;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class General implements FormInterface
{
    /**
     * @var ValidationStrategy
     */
    private $validationStrategy;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ValidationStrategy $validationStrategy,
        UrlInterface $url
    ) {
        $this->validationStrategy = $validationStrategy;
        $this->url = $url;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'import_behavior' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label'])
                                ? __($arguments['label'])
                                : __('Import Behavior')),
                            'componentType' => 'fieldset',
                            'dataScope' => '',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => [
                    'behavior' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Import Behavior'),
                                    'visible' => true,
                                    'dataScope' => 'behavior',
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'dataType' => 'select',
                                    'formElement' => 'select',
                                    'component' => 'Amasty_ImportCore/js/form/element/behavior-select',
                                    'componentType' => 'select',
                                    'additionalClasses' => 'amimportcore-field',
                                    'sortOrder' => 10,
                                    'fieldsUrl' => $this->url->getUrl('amimport/import/requiredFields'),
                                    'entityCode' => $entityConfig->getEntityCode(),
                                    'fieldsProvider' => $arguments['fieldsProvider']
                                        ? $arguments['fieldsProvider'] : '',
                                    'imports' => [
                                        'fields' => '${ $.fieldsProvider }',
                                        'autofill' => '${ $.parentName }.autofill:value',
                                        '__disableTmpl' => ['fields' => false, 'autofill' => false]
                                    ],
                                    'options' => [
                                        ['label' => __('Please Select...'), 'value' => '']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'validation_strategy' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Validation Strategy'),
                                    'visible' => true,
                                    'dataScope' => 'validation_strategy',
                                    'dataType' => 'select',
                                    'formElement' => 'select',
                                    'componentType' => 'select',
                                    'switcherConfig' => [
                                        'enabled' => true,
                                        'rules' => [
                                            [
                                                'value' => 0,
                                                'actions' => [
                                                    [
                                                        'target' => 'index = allow_errors_count',
                                                        'callback' => 'visible',
                                                        'params' => [false]
                                                    ],
                                                    [
                                                        'target' => 'index = allow_errors_count',
                                                        'callback' => 'visible',
                                                        'params' => [false]
                                                    ]
                                                ]
                                            ],
                                            [
                                                'value' => 1,
                                                'actions' => [
                                                    [
                                                        'target' => 'index = allow_errors_count',
                                                        'callback' => 'visible',
                                                        'params' => [true]
                                                    ],
                                                    [
                                                        'target' => 'index = allow_errors_count',
                                                        'callback' => 'visible',
                                                        'params' => [true]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'additionalClasses' => 'amimportcore-field',
                                    'sortOrder' => 20,
                                    'options' => $this->validationStrategy->toOptionArray()
                                ]
                            ]
                        ]
                    ],
                    'allow_errors_count' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Allowed Errors Count'),
                                    'visible' => true,
                                    'dataScope' => 'allow_errors_count',
                                    'dataType' => 'text',
                                    'formElement' => 'input',
                                    'componentType' => 'input',
                                    'additionalClasses' => 'amimportcore-field',
                                    'sortOrder' => 30,
                                    'notice' => __('Please specify number of errors to halt import process.'),
                                    'default' => 10
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if ($entityConfig->getBehaviors()) {
            foreach ($entityConfig->getBehaviors() as $behavior) {
                $result['import_behavior']['children']['behavior']['arguments']['data']['config']['options'][] =
                    ['label' => $behavior->getName(), 'value' => $behavior->getCode()];
            }
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $result = [];

        if ($behavior = $profileConfig->getBehavior()) {
            $result['behavior'] = $behavior;
        }

        $validationStrategy = $profileConfig->getValidationStrategy();
        if ($validationStrategy !== null) {
            $result['validation_strategy'] = $validationStrategy;
        }

        $allowErrorsCount = $profileConfig->getAllowErrorsCount();
        if ($allowErrorsCount !== null) {
            $result['allow_errors_count'] = $allowErrorsCount;
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if ($behavior = $request->getParam('behavior')) {
            $profileConfig->setBehavior($behavior);
        }

        $validationStrategy = $request->getParam('validation_strategy');
        if ($validationStrategy !== null) {
            $profileConfig->setValidationStrategy($validationStrategy);
        }

        $allowErrorsCount = $request->getParam('allow_errors_count');
        if ($allowErrorsCount !== null) {
            $profileConfig->setAllowErrorsCount($allowErrorsCount);
        }

        return $this;
    }
}
