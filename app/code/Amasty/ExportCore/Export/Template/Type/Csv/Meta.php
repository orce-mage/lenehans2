<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Template\Type\Csv;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Amasty\ExportCore\Export\Utils\StaticViewFileResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * @codeCoverageIgnore
 */
class Meta implements FormInterface
{
    const DATASCOPE = 'extension_attributes.csv_template.';

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var StaticViewFileResolver
     */
    private $staticViewFileResolver;

    public function __construct(
        ConfigInterfaceFactory $configFactory,
        Repository $assetRepo,
        StaticViewFileResolver $staticViewFileResolver
    ) {
        $this->configFactory = $configFactory;
        $this->assetRepo = $assetRepo;
        $this->staticViewFileResolver = $staticViewFileResolver;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        if (!empty($arguments['combineChildRowsImage'])) {
            $combineChildRowsImage = $this->assetRepo->getUrl($arguments['combineChildRowsImage']);
        } else {
            $combineChildRowsImage = $this->assetRepo->getUrl(
                $this->staticViewFileResolver->getFileId('images/merge_rows.gif')
            );
        }

        return [
            'csv.has_header_row' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Add Header Row'),
                            'dataType' => 'boolean',
                            'prefer' => 'toggle',
                            'dataScope' => self::DATASCOPE . 'has_header_row',
                            'valueMap' => ['true' => '1', 'false' => '0'],
                            'default' => '1',
                            'formElement' => 'checkbox',
                            'visible' => true,
                            'componentType' => 'field'
                        ]
                    ]
                ]
            ],
            'csv.combine_child_rows' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Merge Rows into One'),
                            'dataType' => 'boolean',
                            'prefer' => 'toggle',
                            'additionalClasses' => 'amexportcore-checkbox -type',
                            'dataScope' => self::DATASCOPE . 'combine_child_rows',
                            'valueMap' => ['true' => '1', 'false' => '0'],
                            'default' => '',
                            'formElement' => 'checkbox',
                            'visible' => true,
                            'componentType' => 'field',
                            'notice' => __('Data from multiple rows will be merged into one cell, if enabled.'),
                            'tooltipTpl' => 'Amasty_ExportCore/form/element/tooltip',
                            'tooltip' => [
                                'description' => '<img src="' . $combineChildRowsImage . '"/>'
                            ],
                            'switcherConfig' => [
                                'enabled' => true,
                                'rules'   => [
                                    [
                                        'value'   => 0,
                                        'actions' => [
                                            [
                                                'target'   => 'index = csv.child_rows.delimiter',
                                                'callback' => 'visible',
                                                'params'   => [false]
                                            ]
                                        ]
                                    ],
                                    [
                                        'value'   => 1,
                                        'actions' => [
                                            [
                                                'target'   => 'index = csv.child_rows.delimiter',
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
            'csv.child_rows.delimiter' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Merged Rows Data Delimiter'),
                            'dataType' => 'text',
                            'default' => Config::SETTING_CHILD_ROW_SEPARATOR,
                            'formElement' => 'input',
                            'visible' => true,
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'child_row_separator',
                            'validation' => [
                                'required-entry' => true
                            ],
                            'notice' => __('The character that delimits each field of the child rows.')
                        ]
                    ]
                ]
            ],
            'csv.separator' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Field Delimiter'),
                            'dataType' => 'text',
                            'default' => Config::SETTING_FIELD_DELIMITER,
                            'formElement' => 'input',
                            'visible' => true,
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'separator',
                            'notice' => __('The character that delimits each field of the rows.'),
                            'validation' => [
                                'max_text_length' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'csv.enclosure' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Field Enclosure Character'),
                            'dataType' => 'text',
                            'default' => Config::SETTING_FIELD_ENCLOSURE_CHARACTER,
                            'visible' => true,
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'enclosure',
                            'notice' => __('The character that encloses each field of the rows.'),
                            'validation' => [
                                'max_text_length' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'csv.postfix' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Entity Key Delimiter'),
                            'dataType' => 'text',
                            'default' => Config::SETTING_POSTFIX,
                            'visible' => true,
                            'formElement' => 'input',
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'postfix',
                            'notice' => __('The character that separates the entity key from the column name.')
                        ]
                    ]
                ]
            ]
        ];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();
        $requestConfig = $request->getParam('extension_attributes')['csv_template'] ?? [];
        if (isset($requestConfig['has_header_row'])) {
            $config->setHasHeaderRow((bool)$requestConfig['has_header_row']);
        }
        if (isset($requestConfig['combine_child_rows'])) {
            $config->setCombineChildRows((bool)$requestConfig['combine_child_rows']);
            $config->setChildRowSeparator((string)$requestConfig['child_row_separator']);
        }
        if (isset($requestConfig['enclosure'])) {
            $config->setEnclosure((string)$requestConfig['enclosure']);
        }
        if (isset($requestConfig['separator'])) {
            $config->setSeparator((string)$requestConfig['separator']);
        }
        if (isset($requestConfig['postfix'])) {
            $config->setPostfix((string)$requestConfig['postfix']);
        }

        $profileConfig->getExtensionAttributes()->setCsvTemplate($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if ($config = $profileConfig->getExtensionAttributes()->getCsvTemplate()) {
            return [
                'extension_attributes' => [
                    'csv_template' => [
                        'has_header_row' => $config->isHasHeaderRow() ? '1' : '0',
                        'combine_child_rows' => $config->isCombineChildRows() ? '1' : '0',
                        'child_row_separator' => $config->getChildRowSeparator(),
                        'enclosure' => $config->getEnclosure(),
                        'separator' => $config->getSeparator(),
                        'postfix' => $config->getPostfix()
                    ]
                ]
            ];
        }

        return [];
    }
}
