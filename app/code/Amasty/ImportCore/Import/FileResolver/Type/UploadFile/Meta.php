<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\UploadFile;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\File\Size;
use Magento\Framework\UrlInterface;

/**
 * @codeCoverageIgnore
 */
class Meta implements FormInterface
{
    const TYPE_ID = 'upload_file';

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    public function __construct(
        Size $fileSize,
        UrlInterface $url,
        ConfigInterfaceFactory $configFactory
    ) {
        $this->fileSize = $fileSize;
        $this->url = $url;
        $this->configFactory = $configFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        return [
            'upload_file' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Select File To Import'),
                            'btnLabel' => __('Select the File'),
                            'notice' => __(
                                'Make sure your file isn\'t more than %1M '
                                . 'and it is saved in UTF-8 encoding for proper import.',
                                $this->fileSize->getMaxFileSizeInMb()
                            ),
                            'dataType' => 'text',
                            'validation' => [
                                'required-entry' => true
                            ],
                            'visible' => true,
                            'componentType' => 'field',
                            'uploaderConfig' => [
                                'url' => $this->url->getUrl('amimport/import/upload')
                            ],
                            'component' => 'Amasty_ImportCore/js/file-uploader',
                            'formElement' => 'fileUploader',
                            'dataScope' => 'file_config.upload_file.file',
                            'template' => 'Amasty_ImportCore/upload-file/upload',
                            'previewTmpl' => 'Amasty_ImportCore/upload-file/preview',
                            'imports' => [
                                'allowedExtensions' => '${ $.provider }:data.'
                                    . ($arguments['fileSourceTypeDataScope'] ?? 'source_type'),
                                '__disableTmpl' => ['allowedExtensions' => false]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();

        if (isset($request->getParam('file_config')['upload_file']['file'][0]['name'])) {
            $config->setHash($request->getParam('file_config')['upload_file']['file'][0]['name']);
        }

        $profileConfig->getExtensionAttributes()->setUploadFileResolver($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        $config = $profileConfig->getExtensionAttributes()->getUploadFileResolver();
        if ($config) {
            return [
                'file_config' => [
                    'upload_file' => [
                        'file' => [
                            ['name' => $config->getHash()]
                        ]
                    ]
                ]
            ];
        }

        return [];
    }
}
