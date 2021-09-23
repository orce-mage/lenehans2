<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\FileResolver\Type\UploadFile;

use Amasty\ImportCore\Api\Config\ProfileConfigExtension;
use Amasty\ImportCore\Import\Config\ProfileConfig;
use Amasty\ImportCore\Import\FileResolver\Type\UploadFile\Config;
use Amasty\ImportCore\Import\FileResolver\Type\UploadFile\FileResolver;
use Amasty\ImportCore\Import\ImportProcess;
use Amasty\ImportCore\Import\Utils\TmpFileManagement;
use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\Collection;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\CollectionFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResolverTest extends TestCase
{
    const HASH = 'test';
    const SOURCE_TYPE = 'csv';
    const FILENAME = 'test';
    const PATH = 'test/path';

    /**
     * @var FileResolver
     */
    private $resolver;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var TmpFileManagement|MockObject
     */
    private $tmpFileManagementMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var ImportProcess|MockObject
     */
    private $importProcessMock;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->importProcessMock = $this->createPartialMock(
            ImportProcess::class,
            ['getProfileConfig', 'getIdentity']
        );

        $uploadFileResolver = $this->createPartialMock(
            Config::class,
            ['getHash']
        );
        $uploadFileResolver->expects($this->any())
            ->method('getHash')
            ->willReturn(self::HASH);
        $profileConfigExtension = $this->createPartialMock(
            ProfileConfigExtension::class,
            ['getUploadFileResolver']
        );
        $profileConfigExtension->expects($this->any())
            ->method('getUploadFileResolver')
            ->willReturn($uploadFileResolver);
        $profileConfig = $this->createPartialMock(
            ProfileConfig::class,
            ['getExtensionAttributes', 'getSourceType']
        );
        $profileConfig->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($profileConfigExtension);
        $profileConfig->expects($this->any())
            ->method('getSourceType')
            ->willReturn(self::SOURCE_TYPE);
        $this->importProcessMock->expects($this->any())
            ->method('getProfileConfig')
            ->willReturn($profileConfig);

        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'getFirstItem']
        );
        $collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->filesystemMock = $this->createPartialMock(
            Filesystem::class,
            ['getDirectoryWrite']
        );
        $this->tmpFileManagementMock = $this->createPartialMock(
            TmpFileManagement::class,
            [
                'getTempDirectory',
                'createTempFile'
            ]
        );
        $this->resolver = $objectManager->getObject(
            FileResolver::class,
            [
                'filesystem' => $this->filesystemMock,
                'collectionFactory' => $collectionFactory,
                'tmpFileManagement' => $this->tmpFileManagementMock
            ]
        );
    }

    public function testExecute()
    {
        $fileUploadMap = $this->createMock(
            FileUploadMap::class
        );
        $fileUploadMap->expects($this->any())
            ->method('__call')
            ->willReturnMap([
                ['getFilename', [], self::FILENAME],
                ['getFileext', [], self::SOURCE_TYPE]
            ]);
        $this->collectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($fileUploadMap);

        $tmpDirRoot = $this->createPartialMock(
            Write::class,
            ['isFile', 'readFile']
        );
        $tmpDirRoot->expects($this->any())
            ->method('isFile')
            ->with(self::FILENAME)
            ->willReturn(true);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($tmpDirRoot);

        $this->importProcessMock->expects($this->any())
            ->method('getIdentity')
            ->willReturn('test');
        $tmpDir = $this->createPartialMock(
            Write::class,
            ['writeFile', 'getAbsolutePath']
        );
        $this->tmpFileManagementMock->expects($this->any())
            ->method('getTempDirectory')
            ->willReturn($tmpDir);
        $this->tmpFileManagementMock->expects($this->any())
            ->method('createTempFile')
            ->willReturn(self::FILENAME);

        $tmpDir->expects($this->once())
            ->method('getAbsolutePath')
            ->with(self::FILENAME)
            ->willReturn(self::PATH);

        $this->assertEquals(self::PATH, $this->resolver->execute($this->importProcessMock));
    }

    public function testExecuteNoFileUploadMap()
    {
        $this->collectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn(null);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Something went wrong.');
        $this->resolver->execute($this->importProcessMock);
    }

    public function testExecuteNoFile()
    {
        $fileUploadMap = $this->createMock(
            FileUploadMap::class
        );
        $fileUploadMap->expects($this->any())
            ->method('__call')
            ->willReturnMap([
                ['getFilename', [], self::FILENAME],
            ]);
        $this->collectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($fileUploadMap);

        $tmpDirRoot = $this->createPartialMock(
            Write::class,
            ['isFile']
        );
        $tmpDirRoot->expects($this->any())
            ->method('isFile')
            ->with(self::FILENAME)
            ->willReturn(false);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($tmpDirRoot);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('File does not exist.');
        $this->resolver->execute($this->importProcessMock);
    }

    public function testExecuteWrongFormat()
    {
        $fileUploadMap = $this->createMock(
            FileUploadMap::class
        );
        $fileUploadMap->expects($this->any())
            ->method('__call')
            ->willReturnMap([
                ['getFilename', [], self::FILENAME],
                ['getFileext', [], 'xml']
            ]);
        $this->collectionMock->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($fileUploadMap);

        $tmpDirRoot = $this->createPartialMock(
            Write::class,
            ['isFile']
        );
        $tmpDirRoot->expects($this->any())
            ->method('isFile')
            ->with(self::FILENAME)
            ->willReturn(true);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($tmpDirRoot);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The import file doesn\'t match the selected format.');
        $this->resolver->execute($this->importProcessMock);
    }
}
