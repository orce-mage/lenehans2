<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Type;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Magento\Framework\UrlInterface;

abstract class AbstractMeta implements FormInterface
{
    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        SourceConfigInterface $sourceConfig,
        UrlInterface $url
    ) {
        $this->sourceConfig = $sourceConfig;
        $this->url = $url;
    }

    protected function getSampleLinkMeta(EntityConfigInterface $entityConfig, string $type): array
    {
        $result = [];
        $sourceConfig = $this->sourceConfig->get($type);
        if (!empty($entityConfig->getFieldsConfig()->getSampleData())
            && !empty($sourceConfig['sampleFileGenerator'])) {
            $result = [
                'sampleData' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'template' => 'Amasty_ImportCore/sample-file',
                                'label' => '',
                                'linkText' => __('Download Sample File'),
                                'sortOrder' => 5,
                                'downloadLink' => $this->url->getUrl(
                                    'amimport/import/download',
                                    [
                                        'entity_code' => $entityConfig->getEntityCode(),
                                        'source' => $type
                                    ]
                                ),
                                'visible' => true,
                                'formElement' => 'container'
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $result;
    }
}
