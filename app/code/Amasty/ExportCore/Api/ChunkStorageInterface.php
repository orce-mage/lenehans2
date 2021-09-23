<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api;

interface ChunkStorageInterface
{
    public function saveChunk(array $data, int $chunkId): \Amasty\ExportCore\Api\ChunkStorageInterface;
    public function readChunk(int $chunkId): array;
    public function chunkSize(int $chunkId): int;
    public function deleteChunk(int $chunkId): \Amasty\ExportCore\Api\ChunkStorageInterface;
    public function getAllChunkIds(): array;
}
