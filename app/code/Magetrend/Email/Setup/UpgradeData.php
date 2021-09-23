<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade data
 */
class UpgradeData implements UpgradeDataInterface
{
    public $emailCollectionFactory;

    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $emailCollectionFactory
    ) {
        $this->emailCollectionFactory = $emailCollectionFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->upgradeData203();
        }

        $setup->endSetup();
    }

    public function upgradeData203()
    {
        $emailCollection = $this->emailCollectionFactory->create()
            ->addFieldToFilter('is_mt_email', 1);

        if ($emailCollection->getSize() == 0) {
            return;
        }

        foreach ($emailCollection as $emailTemplate) {
            $text = $emailTemplate->getData('template_text');
            $text = str_replace('template_id=$this.id', 'template_id=$this.template_id', $text);
            $emailTemplate->setData('template_text', $text);

            $vars = $emailTemplate->getData('orig_template_variables');
            $vars = str_replace('template_id=$this.id', 'template_id=$this.template_id', $vars);
            $emailTemplate->setData('orig_template_variables', $vars);

            $emailTemplate->setData('is_legacy', 1);
        }

        $emailCollection->walk('save');
    }
}
