<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Amasty\GiftCard\Api\GiftCardPriceRepositoryInterface;
use Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\EmailTemplate;
use Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\Lifetime;
use Amasty\GiftCard\Model\Config\Source\GiftCardCodePool;
use Amasty\GiftCard\Model\GiftCard\Attribute\Backend\GiftCard\Price;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPrice;
use Amasty\GiftCard\Model\GiftCard\ResourceModel\GiftCardPriceCollectionFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Area;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateDataTo200
{
    const OLD_MEDIA_IMAGE_PATH = 'amasty_giftcard/';
    const NEW_MEDIA_IMAGE_PATH = 'amasty/amgcard/image/';

    private $changedConfigFields = [
        'display_options/show_options' => ConfigProvider::GIFT_CARD_FIELDS,
        'display_options/allow_users_upload_own_images' => ConfigProvider::ALLOW_USER_IMAGES,
        'display_options/tooltip_content' => ConfigProvider::IMAGE_UPLOAD_TOOLTIP,
        'email/email_identity' => ConfigProvider::EMAIL_SENDER,
        'email/email_recepient_cc' => ConfigProvider::EMAIL_RECIPIENTS,
        'email/email_template_notify' => ConfigProvider::EMAIL_EXPIRATION_TEMPLATE,
        'email/email_template_confirmation_to_sender' => ConfigProvider::EMAIL_SENDER_CONFIRMATION_TEMPLATE,
        'email/attach_gift_card' => ConfigProvider::ATTACH_PDF_GIFT_CARD
    ];

    /**
     * @var State
     */
    private $appState;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var GiftCardPriceRepositoryInterface
     */
    private $giftCardPriceRepository;

    /**
     * @var GiftCardPriceCollectionFactory
     */
    private $priceCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        State $appState,
        EavSetupFactory $eavSetupFactory,
        Filesystem $filesystem,
        Config $scopeConfig,
        ProductMetadataInterface $productMetadata,
        GiftCardPriceRepositoryInterface $giftCardPriceRepository,
        GiftCardPriceCollectionFactory $priceCollectionFactory,
        CollectionFactory $orderItemCollectionFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->giftCardPriceRepository = $giftCardPriceRepository;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup)
    {
        $this->updateAttributes($setup);

        if ($this->productMetadata->getEdition() != 'Community') {
            $this->updatePrices($setup);
        }
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateModuleData']);
    }

    public function updateModuleData()
    {
        $this->moveImages();
        $this->updateConfig();
        $this->updateOrderItems();
        $this->updateProducts();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function updateAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::GIFTCARD_PRICES,
            'backend_model',
            Price::class
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::ALLOW_OPEN_AMOUNT,
            'is_required',
            false
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::ALLOW_OPEN_AMOUNT,
            'used_in_product_listing',
            true
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::FEE_ENABLE,
            'is_required',
            false
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::GIFTCARD_LIFETIME,
            'backend_model',
            Lifetime::class
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::EMAIL_TEMPLATE,
            'backend_model',
            EmailTemplate::class
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::CODE_SET,
            'backend_model',
            ''
        );
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::CODE_SET,
            'source_model',
            GiftCardCodePool::class
        );

        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            Attributes::IMAGE,
            'is_global',
            ScopedAttributeInterface::SCOPE_STORE
        );
    }

    /**
     * Update price only for Magento EE because of row_id is primary field in product_entity
     *
     * @param ModuleDataSetupInterface $setup
     */
    protected function updatePrices(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();

        $priceTable = $setup->getTable(GiftCardPrice::TABLE_NAME);
        $productTable = $setup->getTable('catalog_product_entity');

        foreach ($setup->getConnection()->getForeignKeys($priceTable) as $foreignKey) {
            if ($foreignKey['COLUMN_NAME'] == GiftCardPriceInterface::PRODUCT_ID) {
                $setup->getConnection()->dropForeignKey(
                    $priceTable,
                    $foreignKey['FK_NAME']
                );
            }
        }
        $select = $setup->getConnection()->select()->from(
            ['am_price' => $priceTable],
            [GiftCardPriceInterface::PRICE_ID]
        )->joinLeft(
            ['product' => $productTable],
            'am_price.' . GiftCardPriceInterface::PRODUCT_ID . ' = product.entity_id',
            ['row_id']
        );
        $rowIds = $setup->getConnection()->fetchAssoc($select);
        $prices = $this->priceCollectionFactory->create()->getItems();

        /** @var \Amasty\GiftCard\Api\Data\GiftCardPriceInterface $price */
        foreach ($prices as $price) {
            if ($row = $rowIds[$price->getPriceId()] ?? '') {
                $price->setProductId((int)$row['row_id']);
                try {
                    $this->giftCardPriceRepository->save($price);
                } catch (LocalizedException $e) {
                    $this->logger->error($e);
                }
            }
        }

        $setup->getConnection()->addForeignKey(
            $setup->getConnection()->getForeignKeyName(
                $priceTable,
                GiftCardPriceInterface::PRODUCT_ID,
                $productTable,
                'row_id'
            ),
            $priceTable,
            GiftCardPriceInterface::PRODUCT_ID,
            $productTable,
            'row_id'
        );

        $setup->endSetup();
    }

    /**
     * remove custom gift card amount product option from order items and update amount field
     */
    protected function updateOrderItems()
    {
        $collection = $this->orderItemCollectionFactory->create()
            ->addFieldToFilter('product_type', GiftCard::TYPE_AMGIFTCARD);

        foreach ($collection->getItems() as $orderItem) {
            $customAmount = $orderItem->getProductOptionByCode(GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT);
            $amount = $orderItem->getProductOptionByCode(GiftCardOptionInterface::GIFTCARD_AMOUNT);
            $productOptions = $orderItem->getProductOptions();

            if ($customAmount && $amount == 'custom') {
                $productOptions[GiftCardOptionInterface::GIFTCARD_AMOUNT] = $customAmount;
                unset($productOptions[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT]);
            }
            $orderRate = $orderItem->getOrder()->getBaseToOrderRate();

            if ($orderRate && $orderRate != 1) {
                $productOptions[GiftCardOptionInterface::GIFTCARD_AMOUNT] =
                    $productOptions[GiftCardOptionInterface::GIFTCARD_AMOUNT] / $orderRate;

                if ($buyRequest = $productOptions['info_buyRequest'] ?? []) {
                    if ($customAmount = $buyRequest[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT] ?? '') {
                        $buyRequest[GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT] = $customAmount / $orderRate;
                    }
                    $productOptions['info_buyRequest'] = $buyRequest;
                }
            }

            try {
                $orderItem->setProductOptions($productOptions);
                $this->orderItemRepository->save($orderItem);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Update use config filed in gift card products
     */
    protected function updateProducts()
    {
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('type_id', GiftCard::TYPE_AMGIFTCARD)
            ->create();
        $giftCardProducts = $this->productRepository->getList($searchCriteria);

        foreach ($giftCardProducts->getItems() as $product) {
            try {
                $productStoreIds = $product->getStoreIds();
                array_unshift($productStoreIds, 0);

                foreach ($productStoreIds as $storeId) {
                    $product = $this->productRepository->getById($product->getId(), false, $storeId);

                    if (!$product->getData('am_giftcard_lifetime')) {
                        $product->setData(Attributes::GIFTCARD_LIFETIME, Attributes::ATTRIBUTE_CONFIG_VALUE);
                    }

                    if ($product->getData('am_email_template') == 'amgiftcard_email_email_template') {
                        $product->setData(Attributes::EMAIL_TEMPLATE, Attributes::ATTRIBUTE_CONFIG_VALUE);
                    }
                    $this->storeManager->getStore()->setId($storeId);
                    $this->productRepository->save($product);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Move images to new directory
     */
    protected function moveImages()
    {
        try {
            $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

            if ($writer->isDirectory(self::OLD_MEDIA_IMAGE_PATH)) {
                $writer->create(self::NEW_MEDIA_IMAGE_PATH);
                $mediaPath = $writer->getAbsolutePath();
                $writer->getDriver()->rename(
                    $mediaPath . self::OLD_MEDIA_IMAGE_PATH,
                    $mediaPath . self::NEW_MEDIA_IMAGE_PATH
                );
                $writer->getDriver()->rename(
                    $mediaPath . self::NEW_MEDIA_IMAGE_PATH . 'image_templates',
                    $mediaPath . self::NEW_MEDIA_IMAGE_PATH . 'admin_upload'
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Update config pathes
     */
    protected function updateConfig()
    {
        foreach ($this->changedConfigFields as $oldPath => $newPath) {
            $oldPathData = $this->getConfigValues($oldPath);

            if (!$oldPathData) {
                continue;
            }
            foreach ($oldPathData as $record) {
                $this->changeConfigData($oldPath, $record);

                $this->scopeConfig->saveConfig(
                    'amgiftcard/' . $newPath,
                    $record['value'],
                    $record['scope'],
                    $record['scope_id']
                );
                $this->scopeConfig->deleteConfig(
                    $record['path'],
                    $record['scope'],
                    $record['scope_id']
                );
            }
        }
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws LocalizedException
     */
    private function getConfigValues($path)
    {
        $connection = $this->scopeConfig->getConnection();
        $select = $connection->select()->from(
            $this->scopeConfig->getMainTable()
        )->where(
            'path = ?',
            'amgiftcard/' . $path
        );

        return $connection->fetchAll($select);
    }

    private function changeConfigData($oldPath, &$record)
    {
        switch ($oldPath) {
            case 'email/email_recepient_cc':
                $result = [];

                foreach (explode(',', $record['value']) as $email) {
                    $result[] = trim($email);
                }
                $record['value'] = implode("\r\n", $result);
                break;
            case 'display_options/show_options':
                $fields = explode(',', $record['value']);

                foreach ($fields as $key => $field) {
                    if ($field == 'am_giftcard_sender_email') {
                        unset($fields[$key]);
                    } elseif ($field == 'allow_message') {
                        $fields[$key] = GiftCardOptionInterface::MESSAGE;
                    }
                }
                $record['value'] = implode(',', $fields);
                break;
            case 'email/email_template_confirmation_to_sender':
            case 'email/email_template_notify':
                if ($record['value'] == 'amgiftcard_email_email_template_notify') {
                    $record['value'] = 'amgiftcard_email_email_expiration_template';
                }
                if ($record['value'] == 'amgiftcard_email_email_template_confirmation_to_sender') {
                    $record['value'] = 'amgiftcard_email_email_sender_confirmation_template';
                }
                break;
        }
    }
}
