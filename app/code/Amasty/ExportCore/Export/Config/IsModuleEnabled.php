<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config;

use Magento\Framework\Module\Manager;

class IsModuleEnabled
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager,
        array $config = []
    ) {
        $this->moduleName = $config['moduleName'] ?? '';
        $this->moduleManager = $moduleManager;
    }

    public function isEnabled(): bool
    {
        return (!empty($this->moduleName) && $this->moduleManager->isEnabled($this->moduleName));
    }
}
