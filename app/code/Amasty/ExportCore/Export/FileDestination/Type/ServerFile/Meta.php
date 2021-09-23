<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\FileDestination\Type\ServerFile;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\FormInterface;
use Amasty\ExportCore\Export\Form\Filename\FilenameInput;
use Magento\Framework\App\RequestInterface;

/**
 * @codeCoverageIgnore
 */
class Meta implements FormInterface
{
    const TYPE_ID = 'server_file';
    const DATASCOPE = 'extension_attributes.server_file_destination.';

    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var FilenameInput
     */
    private $filenameInput;

    public function __construct(
        ConfigInterfaceFactory $configFactory,
        FilenameInput $filenameInput
    ) {
        $this->configFactory = $configFactory;
        $this->filenameInput = $filenameInput;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        return array_merge(
            [
                'filepath' => [
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
                                'dataScope' => self::DATASCOPE . 'filepath',
                                'notice' => __(
                                    'The file will be saved in Magento \'var\' directory relative to this path.'
                                )
                            ]
                        ]
                    ]
                ],
                'filename' => $this->filenameInput->get('filename', self::DATASCOPE, __('File Name on Server'))
            ]
        );
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $config = $this->configFactory->create();

        if (isset($request->getParam('extension_attributes')['server_file_destination']['filepath'])) {
            $config->setFilepath($request->getParam('extension_attributes')['server_file_destination']['filepath']);
        }

        if (isset($request->getParam('extension_attributes')['server_file_destination']['filename'])) {
            $config->setFilename($request->getParam('extension_attributes')['server_file_destination']['filename']);
        }

        $profileConfig->getExtensionAttributes()->setServerFileDestination($config);

        return $this;
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        if ($config = $profileConfig->getExtensionAttributes()->getServerFileDestination()) {
            return [
                'extension_attributes' => [
                    'server_file_destination' => [
                        'filepath' => $config->getFilepath(),
                        'filename' => $config->getFilename()
                    ]
                ]
            ];
        }

        return [];
    }
}
