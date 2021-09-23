<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup;

use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Setup\Operation\AddGiftCardAttributes;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * @codeCoverageIgnore
 */
class Uninstall implements UninstallInterface
{
    const GIFT_CARD_MEDIA_DIRECTORY = 'amasty/amgcard';

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        State $appState,
        CollectionFactory $collectionFactory,
        Registry $registry,
        Filesystem $filesystem,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->appState = $appState;
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->filesystem = $filesystem;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Exception
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            [$this, 'changeProduct']
        );

        $setup->startSetup();

        $setup->getConnection()->dropTable(
            $setup->getTable(\Amasty\GiftCard\Model\Code\ResourceModel\Code::TABLE_NAME)
        );
        $setup->getConnection()->dropTable(
            $setup->getTable(\Amasty\GiftCard\Model\CodePool\ResourceModel\CodePoolRule::TABLE_NAME)
        );
        $setup->getConnection()->dropTable(
            $setup->getTable(\Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool::TABLE_NAME)
        );
        $setup->getConnection()->dropTable(
            $setup->getTable(\Amasty\GiftCard\Model\Image\ResourceModel\Image::TABLE_NAME)
        );
        $setup->getConnection()->dropTable(
            $setup->getTable(\Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice::TABLE_NAME)
        );

        $setup->endSetup();

        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE;

        $categorySetup->removeAttribute(
            $entityType,
            Attributes::GIFTCARD_PRICES
        )->removeAttribute(
            $entityType,
            Attributes::ALLOW_OPEN_AMOUNT
        )->removeAttribute(
            $entityType,
            Attributes::OPEN_AMOUNT_MIN
        )->removeAttribute(
            $entityType,
            Attributes::OPEN_AMOUNT_MAX
        )->removeAttribute(
            $entityType,
            Attributes::FEE_ENABLE
        )->removeAttribute(
            $entityType,
            Attributes::FEE_TYPE
        )->removeAttribute(
            $entityType,
            Attributes::FEE_VALUE
        )->removeAttribute(
            $entityType,
            Attributes::GIFTCARD_TYPE
        )->removeAttribute(
            $entityType,
            Attributes::GIFTCARD_LIFETIME
        )->removeAttribute(
            $entityType,
            Attributes::EMAIL_TEMPLATE
        )->removeAttribute(
            $entityType,
            Attributes::CODE_SET
        )->removeAttribute(
            $entityType,
            Attributes::IMAGE
        )->removeAttributeGroup(
            $entityType,
            'Default',
            AddGiftCardAttributes::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME
        );

        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'removeMedia']
        );
    }

    public function changeProduct()
    {
        $this->registry->register('isSecureArea', true);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('type_id', GiftCard::TYPE_AMGIFTCARD)
            ->setFlag('has_stock_status_filter', false)->walk('delete');
        $this->registry->unregister('isSecureArea');
    }

    public function removeMedia()
    {
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        if ($writer->isExist(self::GIFT_CARD_MEDIA_DIRECTORY)) {
            $writer->delete(self::GIFT_CARD_MEDIA_DIRECTORY);
        }
    }
}
