<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model;

use Amasty\GiftCard\Utils\FileUpload;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Math\Random;

class PdfImageConverter
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $manager;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $manager,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->manager = $manager;
        $this->ioFile = $ioFile;
    }

    /**
     * @param string $imageString
     *
     * @return string
     */
    public function convert($imageString): string
    {
        $pdfString = '';

        try {
            $pdfString = $this->createPdfPageFromImageString($imageString);
        } catch (\Exception $e) {
            $this->manager->addErrorMessage(__('Something went wrong. Please review the error log.'));
            $this->logger->critical($e->getMessage());
        }

        return $pdfString;
    }

    /**
     * @param string $imageString
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    private function createPdfPageFromImageString(string $imageString): string
    {
        if (!extension_loaded(FileUpload::PROCESSING_IMAGE_EXT)) {
            return '';
        }
        /** @var \Magento\Framework\Filesystem\Directory\Write $directory */
        $directory = $this->filesystem->getDirectoryWrite(
            DirectoryList::TMP
        );
        $directory->create();

        $ext = $this->ioFile->getPathInfo($imageString)['extension'] ?? '';
        $image = null;

        if ($ext === 'png') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $image = imagecreatefrompng($imageString);
        } elseif ($ext === 'jpg' || $ext === 'jpeg') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $image = imagecreatefromjpeg($imageString);
        } elseif ($ext === 'gif') {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $image = imagecreatefromgif($imageString);
        }

        if (!$image) {
            return '';
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $xSize = imagesx($image);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        $ySize = imagesy($image);

        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage($xSize, $ySize);

        /** @var \Zend_Pdf_Page $page */
        $page = $pdf->pages[0];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imageinterlace($image, 0);
        $tmpFileName = $directory->getAbsolutePath(
            'amasty_gift_card' . uniqid((string)Random::getRandomNumber()) . time() . '.png'
        );
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
        imagepng($image, $tmpFileName);
        $pdfImage = \Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        $directory->delete($directory->getRelativePath($tmpFileName));

        if (is_resource($image)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            imagedestroy($image);
        }

        return $pdf->render();
    }
}
