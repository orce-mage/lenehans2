<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration;

use Amasty\ImportCore\Cron\CleanupBatches;
use Amasty\ImportCore\Import\Utils\TmpFileManagement;
use Amasty\ImportCore\Model\Batch\ResourceModel\Batch as BatchResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CleanupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testCleanup()
    {
        /** @var BatchResource $batchResource */
        $batchResource = $this->objectManager->get(BatchResource::class);
        $batchResource->getConnection()->delete($batchResource->getMainTable());

        /** @var TmpFileManagement $tmpFileManager */
        $tmpFileManager = $this->objectManager->get(TmpFileManagement::class);

        $expiredTemp = $tmpFileManager->getTempDirectory('expired');
        $freshTemp = $tmpFileManager->getTempDirectory('fresh');

        $expiredTemp->touch('testFile.tmp');
        $freshTemp->touch('testFile.tmp');

        $batches = [
            [
                'created_at' => $this->sqlDate('-2 days'),
                'process_identity' => 'expired',
                'batch_data' => '[]'
            ],
            [
                'created_at' => $this->sqlDate('-2 days'),
                'process_identity' => 'expired',
                'batch_data' => '[]'
            ],
            [
                'created_at' => $this->sqlDate('now'),
                'process_identity' => 'fresh',
                'batch_data' => '[]'
            ]
        ];
        $batchResource->getConnection()->insertMultiple($batchResource->getMainTable(), $batches);

        /** @var CleanupBatches $cleanupTask */
        $cleanupTask = $this->objectManager->create(
            CleanupBatches::class,
            ['interval' => '-1 day']
        );
        $cleanupTask->execute();

        $select = $batchResource->getConnection()->select()
            ->from($batchResource->getMainTable());
        $batchData = $batchResource->getConnection()->fetchAll($select);

        $this->assertCount(1, $batchData);
        $this->assertEquals('fresh', $batchData[0]['process_identity']);

        $this->assertDirectoryExists($freshTemp->getAbsolutePath());
        $this->assertDirectoryDoesNotExist($expiredTemp->getAbsolutePath());
    }

    protected function sqlDate(string $relativeDate): string
    {
        $timeZone = new \DateTimeZone('utc');
        $dateTime = new \DateTime($relativeDate, $timeZone);

        return $dateTime->format('Y-m-d h:i:s');
    }
}
