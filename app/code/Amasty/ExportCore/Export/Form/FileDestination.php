<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Form;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FileDestination\FileDestinationConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FileDestination implements FormInterface
{
    /**
     * @var FileDestinationConfigInterface
     */
    private $fileDestinationConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        FileDestinationConfigInterface $fileDestinationConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->fileDestinationConfig = $fileDestinationConfig;
        $this->objectManager = $objectManager;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'file_destination' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Export File')),
                            'componentType' => 'fieldset',
                            'dataScope' => '',
                            'visible' => true,
                            'additionalClasses' => 'amexportcore-fieldset-container'
                        ]
                    ]
                ],
                'children' => []
            ]
        ];

        $fileDestinations = $this->fileDestinationConfig->all();

        foreach ($fileDestinations as $fileDestinationType => $fileDestinationConfig) {
            $result['file_destination']['children'][$fileDestinationType] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __($fileDestinationConfig['name']),
                            'dataType' => 'boolean',
                            'prefer' => 'toggle',
                            'valueMap' => ['true' => $fileDestinationType, 'false' => ''],
                            'default' => '',
                            'formElement' => 'checkbox',
                            'component' => 'Amasty_ExportCore/js/file-destination-checker',
                            'fileDestinationFieldset' => 'destination_' . $fileDestinationType,
                            'visible' => true,
                            'componentType' => 'field',
                            'additionalClasses' => 'amexportcore-nomargin',
                            'dataScope' => 'file_destination_types.' . $fileDestinationType
                        ]
                    ]
                ]
            ];
            if ($metaClass = $this->getFileDestinationMetaClass($fileDestinationType)) {
                $fileDestinationMeta = $metaClass->getMeta($entityConfig, $arguments);
                if (!empty($fileDestinationMeta)) {
                    $result['file_destination']['children']['destination_' . $fileDestinationType] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => '',
                                    'collapsible' => false,
                                    'opened' => true,
                                    'dataScope' => '',
                                    'visible' => true,
                                    'additionalClasses' => 'amexportcore-fieldset',
                                    'componentType' => 'fieldset',
                                ]
                            ]
                        ],
                        'children' => $fileDestinationMeta
                    ];
                }
            }
        }

        return $result;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if (!$profileConfig->getFileDestinationTypes()) {
            return [];
        }

        $result = [];
        foreach ($profileConfig->getFileDestinationTypes() as $fileDestinationType) {
            $result['file_destination_types'][$fileDestinationType] = $fileDestinationType;
            if ($metaClass = $this->getFileDestinationMetaClass($fileDestinationType)) {
                $result = array_merge_recursive($result, $metaClass->getData($profileConfig));
            }
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $fileDestinationTypes = $request->getParam('file_destination_types');
        if (!empty($fileDestinationTypes) && is_array($fileDestinationTypes)) {
            $fileDestinationTypes = array_filter(array_values($fileDestinationTypes));
        } else {
            $fileDestinationTypes = [];
        }

        $profileConfig->setFileDestinationTypes($fileDestinationTypes);
        foreach ($fileDestinationTypes as $fileDestinationType) {
            if ($metaClass = $this->getFileDestinationMetaClass($fileDestinationType)) {
                $metaClass->prepareConfig($profileConfig, $request);
            }
        }

        return $this;
    }

    /**
     * @param string $fileDestinationType
     *
     * @return bool|FormInterface
     * @throws LocalizedException
     */
    private function getFileDestinationMetaClass(string $fileDestinationType)
    {
        $fileDestination = $this->fileDestinationConfig->get($fileDestinationType);
        if (!empty($fileDestination['metaClass'])) {
            $metaClass = $fileDestination['metaClass'];
            if (!is_subclass_of($metaClass, FormInterface::class)) {
                throw new LocalizedException(__('Wrong file destination form class: %1', $metaClass));
            }

            return $this->objectManager->create($metaClass);
        }

        return false;
    }
}
