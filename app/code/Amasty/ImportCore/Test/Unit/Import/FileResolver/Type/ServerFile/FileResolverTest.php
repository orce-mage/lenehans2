<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Test\Unit\Import\FileResolver\Type\ServerFile;

use Amasty\ImportCore\Api\Config\ProfileConfigExtension;
use Amasty\ImportCore\Import\Config\ProfileConfig;
use Amasty\ImportCore\Import\FileResolver\Type\ServerFile\Config;
use Amasty\ImportCore\Import\FileResolver\Type\ServerFile\FileResolver;
use Amasty\ImportCore\Import\ImportProcess;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileResolverTest extends TestCase
{
    const FILE_NAME = 'test';
    const SOURCE_TYPE = 'csv';
    const PATH = 'test/path';

    /**
     * @var FileResolver
     */
    private $resolver;

    /**
     * @var ImportProcess|MockObject
     */
    private $importProcessMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var File|MockObject
     */
    private $fileMock;

    /**
     * @var Config|MockObject
     */
    private $serverFileResolverMock;

    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->importProcessMock = $this->createPartialMock(
            ImportProcess::class,
            ['getProfileConfig', 'getIdentity']
        );

        $this->serverFileResolverMock = $this->createPartialMock(
            Config::class,
            ['getFilename']
        );
        $profileConfigExtension = $this->createPartialMock(
            ProfileConfigExtension::class,
            ['getServerFileResolver']
        );
        $profileConfigExtension->expects($this->any())
            ->method('getServerFileResolver')
            ->willReturn($this->serverFileResolverMock);
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

        $this->filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);
        $this->fileMock = $this->createPartialMock(File::class, ['getPathInfo']);
        $this->resolver = $objectManager->getObject(
            FileResolver::class,
            [
                'filesystem' => $this->filesystemMock,
                'file' => $this->fileMock
            ]
        );
    }

    public function testExecute()
    {
        $this->serverFileResolverMock->expects($this->any())
            ->method('getFilename')
            ->willReturn(self::FILE_NAME);

        $magentoRootDirectory = $this->createPartialMock(
            Read::class,
            ['getAbsolutePath', 'isFile']
        );
        $magentoRootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(self::FILE_NAME)
            ->willReturn(self::PATH);
        $magentoRootDirectory->expects($this->any())
            ->method('isFile')
            ->with(self::FILE_NAME)
            ->willReturn(true);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($magentoRootDirectory);

        $this->fileMock->expects($this->any())
            ->method('getPathInfo')
            ->with(self::PATH)
            ->willReturn(['extension' => self::SOURCE_TYPE]);

        $this->assertEquals(self::PATH, $this->resolver->execute($this->importProcessMock));
    }

    public function testExecuteEmptyFileName()
    {
        $this->serverFileResolverMock->expects($this->any())
            ->method('getFilename')
            ->willReturn('');

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Filename couldn\'t be empty.');
        $this->resolver->execute($this->importProcessMock);
    }

    public function testExecuteNoFile()
    {
        $this->serverFileResolverMock->expects($this->any())
            ->method('getFilename')
            ->willReturn(self::FILE_NAME);

        $magentoRootDirectory = $this->createPartialMock(
            Read::class,
            ['getAbsolutePath', 'isFile']
        );
        $magentoRootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(self::FILE_NAME)
            ->willReturn(self::PATH);
        $magentoRootDirectory->expects($this->any())
            ->method('isFile')
            ->with(self::FILE_NAME)
            ->willReturn(false);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($magentoRootDirectory);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('File with path "' . self::PATH . '" does not exist.');
        $this->resolver->execute($this->importProcessMock);
    }

    public function testExecuteWrongFormat()
    {
        $this->serverFileResolverMock->expects($this->any())
            ->method('getFilename')
            ->willReturn(self::FILE_NAME);

        $magentoRootDirectory = $this->createPartialMock(
            Read::class,
            ['getAbsolutePath', 'isFile']
        );
        $magentoRootDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(self::FILE_NAME)
            ->willReturn(self::PATH);
        $magentoRootDirectory->expects($this->any())
            ->method('isFile')
            ->with(self::FILE_NAME)
            ->willReturn(true);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($magentoRootDirectory);

        $this->fileMock->expects($this->any())
            ->method('getPathInfo')
            ->with(self::PATH)
            ->willReturn(['extension' => 'xml']);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The import file doesn\'t match the selected format.');
        $this->resolver->execute($this->importProcessMock);
    }
}
