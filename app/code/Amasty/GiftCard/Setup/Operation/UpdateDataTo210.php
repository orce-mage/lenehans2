<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup\Operation;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateDataTo210
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var InstallImageData
     */
    private $installImageData;

    public function __construct(
        State $appState,
        InstallImageData $installImageData
    ) {
        $this->appState = $appState;
        $this->installImageData = $installImageData;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup)
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateModuleData']);
    }

    public function updateModuleData()
    {
        $this->installImageData->addImageTemplates(true);
    }
}
