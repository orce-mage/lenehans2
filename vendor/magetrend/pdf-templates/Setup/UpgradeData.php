<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/GiftCard
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-gift-card
 */

namespace Magetrend\PdfTemplates\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade data
 *
 * @category MageTrend
 * @package  Magetend/GiftCard
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-gift-card
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magetrend\PdfTemplates\Model\ResourceModel\Template\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
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
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgrade201($setup, $context);
        }
        $setup->endSetup();
    }

    public function upgrade201($setup, $context)
    {
        $templateCollection = $this->collectionFactory->create();
        if ($templateCollection->getSize() == 0) {
            return;
        }

        foreach ($templateCollection as $template) {
            $template->setSize(\Zend_Pdf_Page::SIZE_A4);
        }

        $templateCollection->walk('save');
    }
}
