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
use Amasty\ExportCore\Api\Template\TemplateConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class Template implements FormInterface
{
    /**
     * @var TemplateConfigInterface
     */
    private $templateConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        TemplateConfigInterface $templateConfig,
        ObjectManagerInterface $objectManager
    ) {
        $this->templateConfig = $templateConfig;
        $this->objectManager = $objectManager;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'template_config' =>  [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('Export Template')),
                            'componentType' => 'fieldset',
                            'dataScope' => '',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => [
                    'template' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('File Type'),
                                    'visible' => true,
                                    'dataScope' => 'template_type',
                                    'dataType' => 'select',
                                    'component' => 'Amasty_ExportCore/js/type-selector',
                                    'additionalClasses' => 'amexportcore-field',
                                    'prefix' => 'template_',
                                    'formElement' => 'select',
                                    'componentType' => 'select'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $templates = $this->templateConfig->all();

        foreach ($templates as $templateType => $templateConfig) {
            $result['template_config']['children']['template']['arguments']['data']['config']['options'][] = [
                'label' => $templateConfig['name'], 'value' => $templateType
            ];

            $children = [];
            if ($metaClass = $this->getTemplateMetaClass($templateType)) {
                $children = array_merge_recursive($children, $metaClass->getMeta($entityConfig, $arguments));
            }

            if (!empty($children)) {
                $result['template_config']['children']['template_' . $templateType] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => '',
                                'additionalClasses' => 'amexportcore-template-type',
                                'collapsible' => false,
                                'dataScope' => '',
                                'opened' => true,
                                'visible' => true,
                                'componentType' => 'fieldset',
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
        if (!$profileConfig->getTemplateType()) {
            return [];
        }
        $result = ['template_type' => $profileConfig->getTemplateType()];

        if ($metaClass = $this->getTemplateMetaClass($profileConfig->getTemplateType())) {
            $result = array_merge_recursive($result, $metaClass->getData($profileConfig));
        }

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        if ($templateType = $request->getParam('template_type')) {
            $profileConfig->setTemplateType($templateType);
            if ($metaClass = $this->getTemplateMetaClass($templateType)) {
                $metaClass->prepareConfig($profileConfig, $request);
            }
        }

        return $this;
    }

    /**
     * @param string $templateType
     *
     * @return bool|FormInterface
     * @throws LocalizedException
     */
    private function getTemplateMetaClass(string $templateType)
    {
        $template = $this->templateConfig->get($templateType);
        if (!empty($template['metaClass'])) {
            $metaClass = $template['metaClass'];
            if (!is_subclass_of($metaClass, FormInterface::class)) {
                throw new LocalizedException(__('Wrong template form class: %1', $metaClass));
            }

            return $this->objectManager->create($metaClass);
        }

        return false;
    }
}
