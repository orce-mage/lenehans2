<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Cron;

use Amasty\ExportCore\Export\Utils\TmpFileManagement;

class Cleanup
{
    /**
     * @var TmpFileManagement
     */
    private $tmp;

    /**
     * @var string
     */
    private $interval;

    public function __construct(
        TmpFileManagement $tmp,
        string $interval = '-1 day'
    ) {
        $this->tmp = $tmp;
        $this->interval = $interval;
    }

    public function execute()
    {
        $deadLine = new \DateTime('now', new \DateTimeZone('utc'));
        $deadLine->modify($this->interval);

        $tmpDir = $this->tmp->getTempDirectory();
        foreach ($tmpDir->read() as $fileName) {
            $stat = $tmpDir->stat($fileName);
            if ($stat['mtime'] < $deadLine->getTimestamp()) {
                $tmpDir->delete($fileName);
            }
        }
    }
}
