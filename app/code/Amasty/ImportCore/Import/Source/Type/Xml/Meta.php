<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Type\Xml;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Amasty\ImportCore\Import\Source\Type\AbstractMeta;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class Meta extends AbstractMeta
{
    const DATASCOPE = 'extension_attributes.xml_source.';

    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    public function __construct(
        SourceConfigInterface $sourceConfig,
        UrlInterface $url,
        ConfigInterfaceFactory $configFactory
    ) {
        parent::__construct($sourceConfig, $url);
        $this->configFactory = $configFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        $result = [
            'xml.item_path' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Data XPath'),
                            'dataType' => 'text',
                            'validation' => [
                                'required-entry' => true
                            ],
                            'dataScope' => self::DATASCOPE . 'item_path',
                            'formElement' => 'input',
                            'visible' => true,
                            'sortOrder' => 10,
                            'componentType' => 'field',
                            'notice' => __('Specify the path to the node, e.g. items/item.')
                        ]
                    ]
                ]
            ]
        ];

        $result = array_merge_recursive(
            $result,
            $this->getSampleLinkMeta($entityConfig, Reader::TYPE_ID)
        );

        return $result;
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();

        if (isset($request->getParam('extension_attributes')['xml_source']['item_path'])) {
            $config->setItemPath($request->getParam('extension_attributes')['xml_source']['item_path']);
        }

        $profileConfig->getExtensionAttributes()->setXmlSource($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if ($config = $profileConfig->getExtensionAttributes()->getXmlSource()) {
            return [
                'extension_attributes' => [
                    'xml_source' => [
                        'item_path' => $config->getItemPath()
                    ]
                ]
            ];
        }

        return [];
    }
}
