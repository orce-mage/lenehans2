<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


require 'giftcard_codepool_with_code.php';
require 'giftcard_image.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard::TYPE_AMGIFTCARD)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Am Gift Card')
    ->setSku('am_giftcard_open_amount')
    ->setDescription('Gift Card Description')
    ->setMetaTitle('Gift Card Meta Title')
    ->setMetaKeyword('Gift Card Meta Keyword')
    ->setMetaDescription('Gift Card Meta Description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->addData([
        'am_allow_open_amount' => '1',
        'am_open_amount_min' => 10,
        'am_open_amount_max' => 200,
        'am_giftcard_type' => '2',
        'am_giftcard_lifetime' => 15,
        'am_email_template' => '-1',
        'am_giftcard_code_set' => $codePool->getCodePoolId(),
        'am_giftcard_code_image' => $gCardImage->getImageId()
    ])->save();
