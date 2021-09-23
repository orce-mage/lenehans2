<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\FileResolver\Type\ServerFile;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\FormInterface;
use Magento\Framework\App\RequestInterface;

/**
 * @codeCoverageIgnore
 */
class Meta implements FormInterface
{
    const TYPE_ID = 'server_file';
    const DATASCOPE = 'extension_attributes.server_file_resolver.';

    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    public function __construct(ConfigInterfaceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        return [
            'filename' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('File Path'),
                            'validation' => [
                                'required-entry' => true
                            ],
                            'dataType' => 'text',
                            'formElement' => 'input',
                            'visible' => true,
                            'componentType' => 'field',
                            'dataScope' => self::DATASCOPE . 'filename',
                            'notice' => __('Use relative path to Magento installation, e.g. var/import/export.csv.')
                        ]
                    ]
                ]
            ]
        ];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();

        if (isset($request->getParam('extension_attributes')['server_file_resolver']['filename'])) {
            $config->setFilename($request->getParam('extension_attributes')['server_file_resolver']['filename']);
        }

        $profileConfig->getExtensionAttributes()->setServerFileResolver($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if ($config = $profileConfig->getExtensionAttributes()->getServerFileResolver()) {
            return [
                'extension_attributes' => [
                    'server_file_resolver' => [
                        'filename' => $config->getFilename()
                    ]
                ]
            ];
        }

        return [];
    }
}
