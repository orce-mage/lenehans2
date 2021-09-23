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
use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class Source implements FormInterface
{
    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        SourceConfigInterface $sourceConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->sourceConfig = $sourceConfig;
        $this->objectManager = $objectManager;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'source_config' =>  [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Import Source')),
                            'componentType' => 'fieldset',
                            'additionalClasses' => 'amimportcore-fieldset',
                            'visible' => true,
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'source' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Import File Type'),
                                    'visible' => true,
                                    'dataScope' => 'source_type',
                                    'source' => 'source_type',
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'dataType' => 'select',
                                    'component' => 'Amasty_ImportCore/js/type-selector',
                                    'prefix' => 'source_',
                                    'formElement' => 'select',
                                    'componentType' => 'select',
                                    'additionalClasses' => 'amimportcore-field -sourceconfig',
                                    'options' => [
                                        ['label' => __('Please Select...'), 'value' => '']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $sources = $this->sourceConfig->all();

        foreach ($sources as $sourceType => $sourceConfig) {
            $result['source_config']['children']['source']['arguments']['data']['config']['options'][] = [
                'label' => $sourceConfig['name'], 'value' => $sourceType
            ];

            $children = [];
            if ($metaClass = $this->getSourceMetaClass($sourceType)) {
                $children = array_merge_recursive($children, $metaClass->getMeta($entityConfig));
            }

            if (!empty($children)) {
                $result['source_config']['children']['source_' . $sourceType] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => '',
                                'collapsible' => false,
                                'opened' => true,
                                'visible' => true,
                                'componentType' => 'fieldset',
                                'additionalClasses' => 'amimportcore-fieldset-container -child',
                                'dataScope' => ''
                            ]
                        ]
                    ],
                    'children' => $children
                ];
            }
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if (!$profileConfig->getSourceType()) {
            return [];
        }

        $result = ['source_type' => $profileConfig->getSourceType()];
        if ($metaClass = $this->getSourceMetaClass($profileConfig->getSourceType())) {
            $result = array_merge_recursive($result, $metaClass->getData($profileConfig));
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if ($source = $request->getParam('source_type')) {
            $profileConfig->setSourceType($source);
            if ($metaClass = $this->getSourceMetaClass($source)) {
                $metaClass->prepareConfig($profileConfig, $request);
            }
        }

        return $this;
    }

    /**
     * @param string $sourceType
     *
     * @return bool|FormInterface
     * @throws LocalizedException
     */
    private function getSourceMetaClass(string $sourceType)
    {
        $source = $this->sourceConfig->get($sourceType);
        if (!empty($source['metaClass'])) {
            $metaClass = $source['metaClass'];
            if (!is_subclass_of($metaClass, FormInterface::class)) {
                throw new LocalizedException(__('Wrong source form class: %1', $metaClass));
            }

            return $this->objectManager->create($metaClass);
        }

        return false;
    }
}
