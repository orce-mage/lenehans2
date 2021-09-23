<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Template\Type\Xml;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;

/**
 * @codeCoverageIgnore
 */
class Meta implements FormInterface
{
    const DATASCOPE = 'extension_attributes.xml_template.';

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    public function __construct(ConfigInterfaceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        return [
            'xml.item' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('XML Item Tag'),
                            'dataType' => 'text',
                            'formElement' => 'input',
                            'visible' => true,
                            'default' => 'item',
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'item'
                        ]
                    ]
                ]
            ],
            'xml.header' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Header'),
                            'dataType' => 'text',
                            'formElement' => 'textarea',
                            'additionalClasses' => 'amexportcore-textarea',
                            'visible' => true,
                            'default' => '<?xml version="1.0"?>' . PHP_EOL . '<items>' . PHP_EOL,
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'header'
                        ]
                    ]
                ]
            ],
            'xml.footer' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Footer'),
                            'dataType' => 'text',
                            'formElement' => 'textarea',
                            'additionalClasses' => 'amexportcore-textarea',
                            'default' => '</items>' . PHP_EOL,
                            'visible' => true,
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'footer'
                        ]
                    ]
                ]
            ],
        ];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();
        $requestConfig = $request->getParam('extension_attributes')['xml_template'] ?? [];
        if (isset($requestConfig['header'])) {
            $config->setHeader((string)$requestConfig['header']);
        }
        if (isset($requestConfig['item'])) {
            $config->setItem((string)$requestConfig['item']);
        }
        if (isset($requestConfig['footer'])) {
            $config->setFooter((string)$requestConfig['footer']);
        }

        $profileConfig->getExtensionAttributes()->setXmlTemplate($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if ($config = $profileConfig->getExtensionAttributes()->getXmlTemplate()) {
            return [
                'extension_attributes' => [
                    'xml_template' => [
                        'header' => $config->getHeader(),
                        'item' => $config->getItem(),
                        'footer' => $config->getFooter()
                    ]
                ]
            ];
        }

        return [];
    }
}
