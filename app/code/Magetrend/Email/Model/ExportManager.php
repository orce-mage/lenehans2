<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class ExportManager
{
    public $templateFactory;

    public $variableCollectionFactory;

    public $jsonHelper;

    public $filesystem;

    public $file;

    private $directory;

    public $driver;

    public function __construct(
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magetrend\Email\Model\ResourceModel\Variable\CollectionFactory $variableCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem\Driver\File $driver
    ) {
        $this->templateFactory = $templateFactory;
        $this->variableCollectionFactory = $variableCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->driver = $driver;
    }

    public function exportTemplates($templateIds = [])
    {
        $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->directory->create();
        $fileList = [];

        foreach ($templateIds as $templateId) {
            $templateFile = $this->exportTemplateData($templateId);
            if (empty($templateFile)) {
                continue;
            }

            $fileList[] = $templateFile;
            $variableFile = $this->exportVariableData($templateId);
            if (!empty($variableFile)) {
                $fileList[] = $variableFile;
            }
        }

        if (empty($fileList)) {
            throw  new LocalizedException(__('No template for export'));
        }


        $packagePath = $this->createPackage($fileList);
        $this->removeFiles($fileList);

        return $packagePath;
    }

    public function removeFiles($fileList)
    {
        foreach ($fileList as $filePath) {
            $rootPath = explode('/', $filePath);
            unset($rootPath[count($rootPath)-1]);
            $rootPath = implode('/', $rootPath);
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            if ($this->driver->isExists($relativePath)) {
                $this->directory->delete($relativePath);
            }
        }
    }

    public function createPackage($fileList)
    {
        if (!class_exists('\ZipArchive')) {
            return;
        }

        $zipFile = $this->directory->getAbsolutePath('export_email_template'.time().'.zip');
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($fileList as $filePath) {
            $rootPath = explode('/', $filePath);
            unset($rootPath[count($rootPath)-1]);
            $rootPath = implode('/', $rootPath);

            $relativePath = substr($filePath, strlen($rootPath) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        $mediaDirectory = $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath('email');

        $mediaFiles = $this->driver->readDirectory($mediaDirectory);
        if (!empty($mediaFiles)) {
            foreach ($mediaFiles as $filePath) {
                if ($this->driver->isDirectory($filePath)) {
                    continue;
                }
                $rootPath = explode('/', $filePath);
                unset($rootPath[count($rootPath)-1]);
                $rootPath = implode('/', $rootPath);
                $relativePath = 'media/email/'.substr($filePath, strlen($rootPath) + 1);

                $zip->addFile($filePath, $relativePath);

            }
        }

        $zip->close();
        return $zipFile;
    }

    public function exportTemplateData($templateId)
    {
        $template = $this->templateFactory->create()
            ->load($templateId);

        if (!$template->getIsMtEmail()) {
            return '';
        }

        $templateData = $this->jsonHelper->jsonEncode($template->getData());
        $fileName = 'email_template_'.$templateId.'_'.time().'.json';
        $path = $this->saveToFile($fileName, $templateData);
        return $path;
    }

    public function exportVariableData($templateId)
    {
        $variableCollection = $this->variableCollectionFactory->create()
            ->addFieldToFilter('template_id', $templateId);

        if ($variableCollection->getSize() == 0) {
            return'';
        }

        $variableList = [];
        foreach ($variableCollection as $variable) {
            $variableList[] = $variable->getData();
        }

        $templateData = $this->jsonHelper->jsonEncode($variableList);
        $fileName = 'email_variable_'.$templateId.'_'.time().'.json';
        $path = $this->saveToFile($fileName, $templateData);
        return $path;
    }

    public function saveToFile($file, $content)
    {
        $filePath = $this->directory->getAbsolutePath($file);
        $this->file->write($filePath, $content);
        return $filePath;
    }
}
