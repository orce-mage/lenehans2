<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Amasty\ExportCore\Api\PostProcessing\PostProcessingConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class PostProcessing implements FormInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PostProcessingConfigInterface
     */
    private $postProcessingConfig;

    public function __construct(
        PostProcessingConfigInterface $postProcessingConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->postProcessingConfig = $postProcessingConfig;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'post_processing' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (
                                isset($arguments['label']) ? __($arguments['label']) : __('Additional Actions')
                            ),
                            'additionalClasses' => 'amexportcore-compress-file',
                            'dataScope' => '',
                            'componentType' => 'fieldset',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        $postProcessors = $this->postProcessingConfig->all();

        foreach ($postProcessors as $type => $config) {
            $result['post_processing']['children'][$type] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __($config['name']),
                            'dataType' => 'boolean',
                            'prefer' => 'toggle',
                            'valueMap' => ['true' => $type, 'false' => ''],
                            'default' => '',
                            'formElement' => 'checkbox',
                            // 'component' => 'Amasty_ExportCore/js/file-destination-checker',
                            // TODO use the same component as in file destinations
                            'processorFieldset' => 'processor_' . $type,
                            'visible' => true,
                            'componentType' => 'field',
                            'dataScope' => 'post_processors.' . $type
                        ]
                    ]
                ]
            ];
            if ($metaClass = $this->getPostProcessorMetaClass($type)) {
                $meta = $metaClass->getMeta($entityConfig);
                if (!empty($meta)) {
                    $result['post_processing']['children']['processor_' . $type] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'collapsible' => false,
                                    'opened' => true,
                                    'visible' => true,
                                    'componentType' => 'fieldset',
                                ]
                            ]
                        ],
                        'children' => $meta
                    ];
                }
            }
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if (!$profileConfig->getPostProcessors()) {
            return [];
        }

        $result = [];
        foreach ($profileConfig->getPostProcessors() as $postProcessorType) {
            $result['post_processors'][$postProcessorType] = $postProcessorType;
            if ($metaClass = $this->getPostProcessorMetaClass($postProcessorType)) {
                $result = array_merge_recursive($result, $metaClass->getData($profileConfig));
            }
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $postProcessors = $request->getParam('post_processors');
        if (!empty($postProcessors) && is_array($postProcessors)) {
            $postProcessors = array_filter(array_values($postProcessors));
        } else {
            $postProcessors = [];
        }
        if (!empty($postProcessors)) {
            $profileConfig->setPostProcessors($postProcessors);
            foreach ($postProcessors as $type) {
                if ($metaClass = $this->getPostProcessorMetaClass($type)) {
                    $metaClass->prepareConfig($profileConfig, $request);
                }
            }
        }

        return $this;
    }

    /**
     * @param string $postProcessorType
     *
     * @return bool|FormInterface
     * @throws LocalizedException
     */
    private function getPostProcessorMetaClass(string $postProcessorType)
    {
        $fileDestination = $this->postProcessingConfig->get($postProcessorType);
        if (!empty($fileDestination['metaClass'])) {
            $metaClass = $fileDestination['metaClass'];
            if (!is_subclass_of($metaClass, FormInterface::class)) {
                throw new LocalizedException(__('Wrong post processor form class: %1', $metaClass));
            }

            return $this->objectManager->create($metaClass);
        }

        return false;
    }
}
