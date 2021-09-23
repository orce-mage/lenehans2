<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import\File;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\Action\FileUploaderInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;

class UploadAction implements ActionInterface
{
    public function initialize(ImportProcessInterface $importProcess): void
    {
        if (empty($importProcess->getEntityConfig()->getFileUploaderConfig())
            || empty($importProcess->getProfileConfig()->getImagesFileDirectory())
        ) {
            return;
        }
        $fileUploader = $importProcess->getEntityConfig()->getFileUploaderConfig()->getFileUploader();
        if (!$fileUploader instanceof FileUploaderInterface) {
            $fileUploaderClass = $importProcess->getEntityConfig()->getFileUploaderConfig()->getFileUploaderClass();
            throw new \RuntimeException(
                'Class ' . $fileUploaderClass . ' doesn\'t implement ' . FileUploaderInterface::class
            );
        }
        $fileUploader->initialize($importProcess);
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        if (empty($importProcess->getEntityConfig()->getFileUploaderConfig())) {
            return;
        }
        $fileUploader = $importProcess->getEntityConfig()->getFileUploaderConfig()->getFileUploader();
        if (!$fileUploader instanceof FileUploaderInterface) {
            $fileUploaderClass = $importProcess->getEntityConfig()->getFileUploaderConfig()->getFileUploaderClass();
            throw new \RuntimeException(
                'Class ' . $fileUploaderClass . ' doesn\'t implement ' . FileUploaderInterface::class
            );
        }
        $fileUploader->execute($importProcess);
    }
}
