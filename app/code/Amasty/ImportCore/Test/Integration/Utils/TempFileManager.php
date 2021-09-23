<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\Utils;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

trait TempFileManager
{
    protected $tmpDirName = 'import_test';

    /**
     * @param string $fixturePath global file path to fixture
     * @return string Returns deployed file path relative to Magento root directory
     */
    public function deployTemporalImportFile(string $fixturePath): string
    {
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $tmpDir = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $tmpDir->create($this->tmpDirName);
        $destFileName = $tmpDir->getAbsolutePath(
            $this->tmpDirName . '/' . sha1($fixturePath) . '.' . pathinfo($fixturePath)['extension']
        );
        symlink($fixturePath, $destFileName);

        return $destFileName;
    }

    public function cleanupTempDirectory()
    {
        /** @var Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $filesystem->getDirectoryWrite(DirectoryList::TMP)->delete('/import_test/');
    }
}
