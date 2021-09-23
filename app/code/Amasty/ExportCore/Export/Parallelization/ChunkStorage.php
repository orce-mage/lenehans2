<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Parallelization;

use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Export\Utils\TmpFileManagement;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;

class ChunkStorage implements ChunkStorageInterface
{
    const CHUNK_FILE_NAME_PREFIX = 'chunk_';

    /**
     * @var string
     */
    private $processIdentity;

    /**
     * @var TmpFileManagement
     */
    private $tmp;

    /**
     * @var DirectoryWriteInterface
     */
    private $tempDirectory;

    public function __construct(
        TmpFileManagement $tmp,
        string $processIdentity
    ) {
        $this->processIdentity = $processIdentity;
        $this->tmp = $tmp;
        $this->tempDirectory = $this->tmp->getTempDirectory($this->processIdentity);
    }

    public function saveChunk(array $data, int $chunkId): ChunkStorageInterface
    {
        $outputFile = $this->createChunkFile($this->tempDirectory, $chunkId);
        $outputFile->write(json_encode($data));
        $outputFile->close();

        return $this;
    }

    public function chunkSize(int $chunkId): int
    {
        return (int)$this->tempDirectory->stat($this->getChunkFileName($chunkId))['size'] ?? 0;
    }

    public function readChunk(int $chunkId): array
    {
        $content = $this->tempDirectory->readFile($this->getChunkFileName($chunkId));

        return json_decode($content, true);
    }

    public function getAllChunkIds(): array
    {
        $result = [];
        foreach ($this->tempDirectory->read() as $fileName) {
            if (strpos($fileName, self::CHUNK_FILE_NAME_PREFIX) === 0) {
                if ($id = (int)substr($fileName, strlen(self::CHUNK_FILE_NAME_PREFIX))) {
                    $result[] = $id;
                }
            }
        }

        return $result;
    }

    public function deleteChunk(int $chunkId): ChunkStorageInterface
    {
        $this->tempDirectory->delete($this->getChunkFileName($chunkId));

        return $this;
    }

    protected function getChunkFileName(int $chunkId): string
    {
        return self::CHUNK_FILE_NAME_PREFIX . $chunkId;
    }

    protected function createChunkFile(DirectoryWriteInterface $directory, int $chunkId): FileWriteInterface
    {
        return $directory->openFile($this->getChunkFileName($chunkId));
    }
}
