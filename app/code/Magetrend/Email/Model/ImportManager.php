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

class ImportManager
{
    public $filesystem;

    public $uploaderFactory;

    public $driver;

    public $file;

    public $jsonHelper;

    public $templateCollectionFactory;

    public $templateFactory;

    public $varCollectionFactory;

    public $varFactory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magetrend\Email\Model\ResourceModel\Variable\CollectionFactory $varCollectionFactory,
        \Magetrend\Email\Model\VariableFactory $varFactory
    ) {
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->driver = $driver;
        $this->file = $file;
        $this->jsonHelper = $jsonHelper;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->templateFactory = $templateFactory;
        $this->varCollectionFactory = $varCollectionFactory;
        $this->varFactory = $varFactory;
    }

    public function importTemplates($uid = '')
    {
        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmp->create();
        $target = $tmp->getAbsolutePath();
        $templateList = $this->loadTemplateList($tmp->getAbsolutePath($uid.'_mt_email_template_import'));
        if (empty($templateList)) {
            throw new LocalizedException(__('Unable to import. Nothing to import'));
        }

        foreach ($templateList as $template) {
            $this->importTemplate($template);
        }

        $this->moveMedia($uid);
        $this->cleanUp();
    }

    public function importTemplate($templateData)
    {
        $template = $this->templateFactory->create();
        $templateCode = $templateData['template_code'];
        $template->load($templateCode, 'template_code');
        if ($template->getId()) {
            return $this->updateTemplate($templateData, $template);
        }

        return $this->addTemplate($templateData);
    }

    public function updateTemplate($templateData, $template)
    {
        $template->addData([
            'template_text' => $templateData['template_text'],
            'template_styles' => $templateData['template_styles'],
            'template_subject' => $templateData['template_subject'],
            'store_id' => $templateData['store_id'],
            'locale' => $templateData['locale'],
            'direction' => $templateData['direction'],
            'is_mt_email' => $templateData['is_mt_email'],
        ])->save();

        $this->updateVariableCollection($template, $templateData);
    }

    public function updateVariableCollection($template, $templateData)
    {
        $collection = $this->varCollectionFactory->create()
            ->addFieldToFilter('template_id', $template->getId());

        if ($collection->getSize() > 0) {
            $collection->walk('delete');
        }

        $variableList = $this->getVariableList($templateData);
        if (empty($variableList)) {
            return false;
        }

        foreach ($variableList as $varData) {
            $this->varFactory->create()
                ->setData($varData)
                ->setId(null)
                ->setHash(null)
                ->setTemplateId($template->getId())
                ->save();
        }

        return true;
    }

    public function getVariableList($templateData)
    {
        $sourceFile = $templateData['source'];
        $sourceFile = explode('/', $sourceFile);
        $fileName = end($sourceFile);
        $relativePath = $sourceFile[count($sourceFile) - 2];

        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmp->create();
        $target = $tmp->getAbsolutePath($relativePath);
        $fileList = $this->driver->readDirectory($target);

        if (empty($fileList)) {
            return;
        }

        $variableSourceFile = '';
        foreach ($fileList as $path) {
            $fname = str_replace($target, '', $path);
            if (strpos($fname, 'email_variable_'.$templateData['template_id'].'_') === false ) {
                continue;
            }

            $variableSourceFile = $path;
            break;
        }

        if (empty($variableSourceFile)) {
            return [];
        }

        $variableList = $this->file->read($variableSourceFile);
        if (empty($variableList)) {
            return [];
        }

        $variableList = $this->jsonHelper->jsonDecode($variableList);
        return $variableList;
    }

    public function addTemplate($templateData)
    {
        $template = $this->templateFactory->create()
            ->setData($templateData)
            ->setId(null)
            ->save();

        $this->updateVariableCollection($template, $templateData);
    }

    public function uploadFile($uid = '')
    {
        $this->cleanUp();

        $fileName = $uid.'_mt_email_template_import.zip';
        $extractTo = $uid.'_mt_email_template_import';

        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmp->create();
        $target = $tmp->getAbsolutePath();

        $uploader = $this->uploaderFactory->create(['fileId' => 'mt_template_files']);
        $uploader->setAllowedExtensions(['zip']);
        $uploader->setAllowRenameFiles(true);
        $result = $uploader->save($target, $fileName);

        if (!class_exists('\ZipArchive')) {
            throw new LocalizedException(__('ZipArchive is not installed'));
        }

        $this->extractFile($tmp, $target.'/'.$fileName, $extractTo);
        $tmp->delete($fileName);

        $templateList = $this->loadTemplateList($tmp->getAbsolutePath($extractTo));
        return $this->prepareInfo($templateList);
    }

    public function prepareInfo($templateList)
    {
        if (empty($templateList)) {
            return ['new' => 0, 'update' => 0];
        }

        $templateCodeList = [];
        foreach ($templateList as $templateData) {
            $templateCodeList[] = $templateData['template_code'];
        }

        $collection = $this->templateCollectionFactory->create()
            ->addFieldToFilter('template_code', ['in' => $templateCodeList]);

        $total = count($templateCodeList);
        return ['new' => ($total - $collection->getSize()), 'update' => $collection->getSize()];
    }

    public function loadTemplateList($path)
    {
        $files = $this->driver->readDirectory($path);
        if (empty($files)) {
            return [];
        }

        $templateList = [];
        foreach ($files as $file) {
            $fileName = str_replace($path, '', $file);
            if (strpos($fileName, 'email_template_') === false) {
                continue;
            }

            $templateContent = $this->file->read($file);
            if (empty($templateContent)) {
                continue;
            }

            $templateData = $this->jsonHelper->jsonDecode($templateContent);
            $templateData['source'] = $file;
            $templateList[] = $templateData;
        }

        return $templateList;
    }

    public function extractFile($directory, $file, $extractTo)
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) === true) {
            $zip->extractTo($directory->getAbsolutePath($extractTo));
            $zip->close();
        } else {
            throw new LocalizedException(__('Unable to extract .zip archive.'));
        }
    }

    public function moveMedia($uid = '')
    {
        $subDir = $uid.'_mt_email_template_import/media/email';
        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmp->create();
        $target = $tmp->getAbsolutePath($subDir);
        if (!$this->driver->isExists($target)) {
            return false;
        }

        $mediaList = $this->driver->readDirectory($target);

        if (empty($mediaList)) {
            return false;
        }

        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDir->create();

        foreach ($mediaList as $mediaFile) {
            $fileName = explode('/', $mediaFile);
            $fileName = end($fileName);

            $this->file->mv($mediaFile, $mediaDir->getAbsolutePath('email/'.$fileName));
        }

        return true;
    }

    public function cleanUp()
    {
        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmp->create();
        $tmpPath = $tmp->getAbsolutePath();
        $tmpDir = $this->driver->readDirectory($tmpPath);

        if (empty($tmpDir)) {
            return;
        }

        foreach ($tmpDir as $filePath) {
            if (strpos($filePath, 'mt_email_template_import') === false) {
                continue;
            }

            $relativeDir = str_replace($tmpPath.'/', '', $filePath);
            $tmp->delete($relativeDir);
        }
    }
}
