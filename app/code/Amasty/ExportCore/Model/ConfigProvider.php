<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    const MULTI_PROCESS = 'multi_process';
    const MULTI_PROCESS_ENABLED = self::MULTI_PROCESS . '/enabled';
    const MULTI_PROCESS_COUNT = self::MULTI_PROCESS . '/max_process_count';

    protected $pathPrefix = 'amasty_export/';

    public function useMultiProcess($storeId = null): bool
    {
        return $this->isSetFlag(self::MULTI_PROCESS_ENABLED, $storeId);
    }

    public function getMaxProcessCount($storeId = null): int
    {
        return (int)$this->getValue(self::MULTI_PROCESS_COUNT, $storeId);
    }
}
