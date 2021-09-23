<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Model\GiftCard\Attributes as GiftCardAttributes;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class AddGiftCardAttributes
{
    const GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME = 'Gift Card Information';

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $this->addAttributes($categorySetup);
    }

    /**
     * @param CategorySetup $categorySetup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addAttributes(CategorySetup $categorySetup)
    {
        $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE;

        $categorySetup->addAttributeGroup(
            $entityType,
            $categorySetup->getDefaultAttributeSetId($entityType),
            self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
            9
        );

        //GiftCard prices attributes

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::GIFTCARD_PRICES,
            [
                'type' => 'decimal',
                'label' => 'Amounts',
                'backend' => \Amasty\GiftCard\Model\GiftCard\Attribute\Backend\GiftCard\Price::class,
                'input' => 'price',
                'source' => '',
                'required' => false,
                'sort_order' => -5,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::ALLOW_OPEN_AMOUNT,
            [
                'backend' => '',
                'frontend' => '',
                'class' => '',
                'source' => '',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'unique' => false,
                'type' => 'int',
                'sort_order' => -4,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'used_in_product_listing' => true,
                'input' => 'boolean',
                'label' => 'Open Amount',
                'default' => 0,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::OPEN_AMOUNT_MIN,
            [
                'type' => 'decimal',
                'label' => 'Open Amount Min Value',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'input' => 'price',
                'source' => '',
                'required' => false,
                'sort_order' => -3,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'class' => 'validate-number',
                'visible' => true,
                'used_in_product_listing' => true,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::OPEN_AMOUNT_MAX,
            [
                'type' => 'decimal',
                'label' => 'Open Amount Max Value',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'input' => 'price',
                'source' => '',
                'required' => false,
                'sort_order' => -2,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'class' => 'validate-number',
                'visible' => true,
                'used_in_product_listing' => true,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD
            ]
        );

        //GiftCard fee attributes

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::FEE_ENABLE,
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'class' => '',
                'source' => '',
                'required' => false,
                'sort_order' => -1,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true,
                'input' => 'boolean',
                'label' => 'Enable Fee for Purchase',
                'default' => 0,
                'is_visible' => 1,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::FEE_TYPE,
            [
                'type' => 'int',
                'label' => 'Add a Fee for Purchase',
                'backend' => '',
                'input' => 'select',
                'source' => \Amasty\GiftCard\Model\Config\Source\Fee::class,
                'required' => false,
                'sort_order' => 0,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::FEE_VALUE,
            [
                'type' => 'decimal',
                'label' => 'Specify Fee Value',
                'backend' => '',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'sort_order' => 1,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => 'Amasty Gift Card Prices',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        // Attributes to gift card tab

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::GIFTCARD_TYPE,
            [
                'type' => 'int',
                'label' => 'Card Type',
                'backend' => '',
                'frontend' => '',
                'input' => 'select',
                'source' => \Amasty\GiftCard\Model\Config\Source\GiftCardType::class,
                'required' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::GIFTCARD_LIFETIME,
            [
                'type' => 'int',
                'label' => 'Lifetime (days)',
                'backend' => \Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\Lifetime::class,
                'input' => 'text',
                'source' => '',
                'required' => false,
                'default' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::EMAIL_TEMPLATE,
            [
                'type' => 'varchar',
                'label' => 'Email Template',
                'backend' => \Amasty\GiftCard\Model\Config\Attribute\Backend\UseConfig\EmailTemplate::class,
                'input' => 'select',
                'source' => \Amasty\GiftCard\Model\Config\Source\EmailTemplate::class,
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::CODE_SET,
            [
                'type' => 'int',
                'label' => 'Choose Gift Card Code Pool',
                'backend' => '',
                'input' => 'select',
                'source' => \Amasty\GiftCard\Model\Config\Source\GiftCardCodePool::class,
                'required' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $categorySetup->addAttribute(
            $entityType,
            GiftCardAttributes::IMAGE,
            [
                'type' => 'varchar',
                'label' => 'Choose Gift Card Images',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'input' => 'multiselect',
                'source' => \Amasty\GiftCard\Model\Config\Source\Image::class,
                'required' => true,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => self::GIFT_CARD_INFO_ATTRIBUTE_GROUP_NAME,
                'visible_on_front' => false,
                'apply_to' => GiftCard::TYPE_AMGIFTCARD,
                'visible' => true
            ]
        );

        $this->addAttributeToGift($categorySetup, 'weight');
        $this->addAttributeToGift($categorySetup, 'tax_class_id');
    }

    /**
     * @param CategorySetup $categorySetup
     * @param string $attribute
     */
    private function addAttributeToGift(CategorySetup $categorySetup, string $attribute)
    {
        $applyTo = $categorySetup->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attribute, 'apply_to');

        if ($applyTo) {
            $applyTo = explode(',', $applyTo);

            if (!in_array(GiftCard::TYPE_AMGIFTCARD, $applyTo)) {
                $applyTo[] = GiftCard::TYPE_AMGIFTCARD;
                $categorySetup->updateAttribute(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attribute,
                    'apply_to',
                    join(',', $applyTo)
                );
            }
        }
    }
}
