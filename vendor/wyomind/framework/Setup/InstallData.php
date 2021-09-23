<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Wyomind\GoogleShoppingAction\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $status;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Module\Status $status
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Framework\Module\Status $status
    ) {
    

        $this->state = $state;
        $this->status = $status;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    

        unset($context);
        $installer = $setup;
        $installer->startSetup();
        $installer->endSetup();
    }
}
