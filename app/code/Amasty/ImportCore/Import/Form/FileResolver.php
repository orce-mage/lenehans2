<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Form;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FileResolver\FileResolverConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FileResolver implements FormInterface
{
    /**
     * @var FileResolverConfigInterface
     */
    private $fileResolverConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        FileResolverConfigInterface $fileResolverConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->fileResolverConfig = $fileResolverConfig;
        $this->objectManager = $objectManager;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'file_config' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Import File')),
                            'componentType' => 'fieldset',
                            'visible' => true,
                            'dataScope' => '',
                            'additionalClasses' => 'amimportcore-import-source',
                        ]
                    ]
                ],
                'children' => [
                    'file_source_type' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Import Source'),
                                    'visible' => true,
                                    'dataScope' => 'file_resolver_type',
                                    'validation' => [
                                        'required-entry' => true
                                    ],
                                    'dataType' => 'select',
                                    'component' => 'Amasty_ImportCore/js/type-selector',
                                    'prefix' => 'file_',
                                    'formElement' => 'select',
                                    'componentType' => 'select',
                                    'additionalClasses' => 'amimportcore-field',
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

        $fileResolvers = $this->fileResolverConfig->all();

        foreach ($fileResolvers as $fileResolverType => $fileResolverConfig) {
            $result['file_config']['children']['file_source_type']['arguments']['data']['config']['options'][] = [
                'label' => $fileResolverConfig['name'], 'value' => $fileResolverType
            ];
            if (!($fileResolverMetaClass = $this->getFileResolverMetaClass($fileResolverType))) {
                continue;
            }

            $fileResolverMeta = $fileResolverMetaClass->getMeta($entityConfig, $arguments);
            if (!empty($fileResolverMeta)) {
                $result['file_config']['children']['file_' . $fileResolverType] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => '',
                                'collapsible' => false,
                                'opened' => true,
                                'visible' => true,
                                'componentType' => 'fieldset',
                                'dataScope' => ''
                            ]
                        ]
                    ],
                    'children' => $fileResolverMeta
                ];
            }
        }

        if ($arguments['useImagesFileDirectory'] ?? false) {
            $imageFileDirectory = [
                'file_config' => [
                    'children' => [
                        'images_file_directory' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'label' => __('Images File Directory'),
                                        'visible' => true,
                                        'dataScope' => 'images_file_directory',
                                        'dataType' => 'text',
                                        'formElement' => 'input',
                                        'componentType' => 'input',
                                        'notice' => __('Use relative path to Magento installation, e.g. var/import.')
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $result = array_merge_recursive($result, $imageFileDirectory);
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if (!$profileConfig->getFileResolverType()) {
            return [];
        }

        $result = ['file_resolver_type' => $profileConfig->getFileResolverType()];
        if ($metaClass = $this->getFileResolverMetaClass($profileConfig->getFileResolverType())) {
            $result = array_merge_recursive($result, $metaClass->getData($profileConfig));
        }
        if ($imagesFileDirectory = $profileConfig->getImagesFileDirectory()) {
            $result['images_file_directory'] = $imagesFileDirectory;
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if ($fileResolver = $request->getParam('file_resolver_type')) {
            $profileConfig->setFileResolverType($fileResolver);
            if ($metaClass = $this->getFileResolverMetaClass($fileResolver)) {
                $metaClass->prepareConfig($profileConfig, $request);
            }
        }
        if ($imagesFileDirectory = $request->getParam('images_file_directory')) {
            $profileConfig->setImagesFileDirectory($imagesFileDirectory);
        }

        return $this;
    }

    /**
     * @param string $fileResolverType
     *
     * @return bool|FormInterface
     * @throws LocalizedException
     */
    private function getFileResolverMetaClass(string $fileResolverType)
    {
        $fileResolver = $this->fileResolverConfig->get($fileResolverType);
        if (!empty($fileResolver['metaClass'])) {
            $metaClass = $fileResolver['metaClass'];
            if (!is_subclass_of($metaClass, FormInterface::class)) {
                throw new LocalizedException(__('Wrong file resolver form class: %1', $metaClass));
            }

            return $this->objectManager->create($metaClass);
        }

        return false;
    }
}
