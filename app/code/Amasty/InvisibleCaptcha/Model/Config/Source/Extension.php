<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Module\Manager;

class Extension implements OptionSourceInterface
{
    const EXTENSION_NOT_INSTALLED = -1;
    const INTEGRATION_DISABLED = 0;
    const INTEGRATION_ENABLED = 1;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $extension = '';

    public function __construct(
        Manager $moduleManager,
        $moduleName = ''
    ) {
        $this->moduleManager = $moduleManager;
        $this->extension = $moduleName;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->moduleManager->isEnabled($this->extension)) {
            return [
                ['value' => self::INTEGRATION_DISABLED, 'label' => __('No')],
                ['value' => self::INTEGRATION_ENABLED, 'label' => __('Yes')],
            ];
        }

        return [['value' => self::EXTENSION_NOT_INSTALLED, 'label' => __('Not Installed')]];
    }
}
