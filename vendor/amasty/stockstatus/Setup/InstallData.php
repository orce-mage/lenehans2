<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Setup;

use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Stockstatus\Model\Attribute\Creator;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Creator
     */
    private $attributeCreator;

    /**
     * @var Operation\Examples
     */
    private $examples;

    public function __construct(Creator $attributeCreator, Operation\Examples $examples)
    {
        $this->attributeCreator = $attributeCreator;
        $this->examples = $examples;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $this->attributeCreator->createProductAttribute(
            'custom_stock_status',
            'Custom Stock Status',
            [
                'source' => SourceTable::class
            ]
        );

        $this->examples->execute();
    }
}
