<?php
namespace MageBig\Shopbybrand\Setup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    private $labelSetupFactory;
	public function __construct(BrandSetupFactory $categorySetupFactory)
    {
        $this->brandSetupFactory = $categorySetupFactory;
    }
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		$labelSetup = $this->brandSetupFactory->create(['setup' => $setup]);
        $labelSetup->installEntities();		
	}
}