<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Helper;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Download
 * @package Wyomind\Framework\Helper
 */
class Download extends \Wyomind\Framework\Helper\License
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawFactory;

    /**
     * Download constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Controller\Result\RawFactory $rawFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Controller\Result\RawFactory $rawFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
    
        parent::__construct($objectManager, $context);
        $this->rawFactory = $rawFactory;
    }

    /**
     * Send upload response
     * @param string $fileName
     * @param string $content
     * @param string $contentType
     * @return RawFactory
     */
    public function sendUploadResponse(
        $fileName,
        $content,
        $contentType = "application/octet-stream"
    ) {
    
        $resultRaw = $this->rawFactory->create();
        $resultRaw->setHeader('Content-Type', $contentType)
            ->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true)
            ->setHeader("Content-Disposition", "attachment; filename=" . $fileName)
            ->setHeader("Last-Modified", date("r"))
            ->setHeader("Accept-Ranges", "bytes")
            ->setHeader("Content-Length", strlen($content))
            ->setHttpResponseCode(200)
            ->setContents($content);
        return $resultRaw;
    }
}
