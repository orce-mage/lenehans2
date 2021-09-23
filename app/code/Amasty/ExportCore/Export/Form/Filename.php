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
use Magento\Framework\App\RequestInterface;

class Filename implements FormInterface
{
    /**
     * @var Filename\FilenameInput
     */
    private $filenameInput;

    public function __construct(Filename\FilenameInput $filenameInput)
    {
        $this->filenameInput = $filenameInput;
    }

    public function getMeta(EntityConfigInterface $entityConfig, array $arguments = []): array
    {
        return [
            'filename' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => (isset($arguments['label']) ? __($arguments['label']) : __('File Name')),
                            'componentType' => 'fieldset',
                            'dataScope' => '',
                            'visible' => true,
                        ]
                    ]
                ],
                'children' => [
                    'filename' => $this->filenameInput->get('filename')
                ]
            ]
        ];
    }

    public function getData(ProfileConfigInterface $profileConfig): array
    {
        return ['filename' => $profileConfig->getFilename()];
    }

    public function prepareConfig(ProfileConfigInterface $profileConfig, RequestInterface $request): FormInterface
    {
        $filename = $request->getParam('filename');
        if (!empty($filename)) {
            $profileConfig->setFilename($filename);
        }

        return $this;
    }
}
