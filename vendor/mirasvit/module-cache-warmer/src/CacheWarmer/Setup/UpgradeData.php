<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeDataInterface[]
     */
    private $pool;

    public function __construct(
        UpgradeData\UpgradeData108 $upgrade108,
        UpgradeData\UpgradeData109 $upgrade109,
        UpgradeData\UpgradeData1014 $upgrade1014,
        UpgradeData\UpgradeData1015 $upgrade1015,
        UpgradeData\UpgradeData1016 $upgrade1016,
        UpgradeData\UpgradeData1018 $upgrade1018
    ) {
        $this->pool = [
            '1.0.8' => $upgrade108,
            '1.0.9' => $upgrade109,
            '1.0.14' => $upgrade1014,
            '1.0.15' => $upgrade1015,
            '1.0.16' => $upgrade1016,
            '1.0.18' => $upgrade1018
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        foreach ($this->pool as $version => $upgrade) {
            if (getenv('MST_FPC_TEST_MAX_VERSION') && version_compare(getenv('MST_FPC_TEST_MAX_VERSION'), $version, "<")) {
                break;
            }
            if (version_compare($context->getVersion(), $version, "<")) {
                $upgrade->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }
}
