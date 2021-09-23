<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Integration\Traits;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Zend\Stdlib\Parameters;

trait ImageUpload
{
    private function prepareCustomImage(string $inputName): array
    {
        $objectManager = Bootstrap::getObjectManager();

        $tmpWriter = $objectManager->create(Filesystem::class)->getDirectoryWrite(DirectoryList::TMP);
        $tmpWriter->touch('test');

        $fileArray = [
            'name' => 'test.jpg',
            'tmp_name' => $tmpWriter->getAbsolutePath('test'),
            'type' => 'image/jpg',
            'size' =>  500,
            'error' => 0
        ];
        $_FILES = [
            $inputName => $fileArray
        ];
        /** @var RequestInterface $request */
        $request = $objectManager->get(RequestInterface::class);
        $request->setFiles(new Parameters($_FILES));

        return $fileArray;
    }
}
