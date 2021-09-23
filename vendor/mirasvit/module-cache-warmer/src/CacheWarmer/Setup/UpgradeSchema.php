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
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeSchemaInterface[]
     */
    private $pool;

    public function __construct(
        UpgradeSchema\UpgradeSchema101 $upgrade101,
        UpgradeSchema\UpgradeSchema102 $upgrade102,
        UpgradeSchema\UpgradeSchema103 $upgrade103,
        UpgradeSchema\UpgradeSchema104 $upgrade104,
        UpgradeSchema\UpgradeSchema105 $upgrade105,
        UpgradeSchema\UpgradeSchema106 $upgrade106,
        UpgradeSchema\UpgradeSchema107 $upgrade107,
        UpgradeSchema\UpgradeSchema108 $upgrade108,
        UpgradeSchema\UpgradeSchema109 $upgrade109,
        UpgradeSchema\UpgradeSchema1010 $upgrade1010,
        UpgradeSchema\UpgradeSchema1011 $upgrade1011,
        UpgradeSchema\UpgradeSchema1012 $upgrade1012,
        UpgradeSchema\UpgradeSchema1013 $upgrade1013,
        UpgradeSchema\UpgradeSchema1014 $upgrade1014,
        UpgradeSchema\UpgradeSchema1015 $upgrade1015,
        UpgradeSchema\UpgradeSchema1016 $upgrade1016,
        UpgradeSchema\UpgradeSchema1017 $upgrade1017,
        UpgradeSchema\UpgradeSchema1018 $upgrade1018,
        UpgradeSchema\UpgradeSchema1019 $upgrade1019
    ) {
        $this->pool = [
            '1.0.1'  => $upgrade101,
            '1.0.2'  => $upgrade102,
            '1.0.3'  => $upgrade103,
            '1.0.4'  => $upgrade104,
            '1.0.5'  => $upgrade105,
            '1.0.6'  => $upgrade106,
            '1.0.7'  => $upgrade107,
            '1.0.8'  => $upgrade108,
            '1.0.9'  => $upgrade109,
            '1.0.10' => $upgrade1010,
            '1.0.11' => $upgrade1011,
            '1.0.12' => $upgrade1012,
            '1.0.13' => $upgrade1013,
            '1.0.14' => $upgrade1014,
            '1.0.15' => $upgrade1015,
            '1.0.16' => $upgrade1016,
            '1.0.17' => $upgrade1017,
            '1.0.18' => $upgrade1018,
            '1.0.19' => $upgrade1019,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version, "<")) {
                $upgrade->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }
}
