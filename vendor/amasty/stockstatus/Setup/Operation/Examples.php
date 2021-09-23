<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Setup\Operation;

use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Store\Model\Store;

class Examples
{
    /**
     * @var array
     */
    private $examples = [
        'ONLY {qty} LEFT!',
        'On Sale {special_price}',
        'Will be available {day-after-tomorrow}',
        'Backorder',
        'Product will be available on {expected_date}'
    ];

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private $attributeOptionFactory;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private $optionLabelFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        AttributeOptionInterfaceFactory $attributeOptionFactory
    ) {
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     *  Add default stock status examples
     */
    public function execute()
    {
        $attribute = $this->attributeRepository->get('custom_stock_status');
        if ($attribute) {
            $attributeId = $attribute->getAttributeId();
            foreach ($this->examples as $example) {
                $optionLabel = $this->optionLabelFactory->create();
                $optionLabel->setStoreId(Store::DEFAULT_STORE_ID);
                $optionLabel->setLabel($example);

                $option = $this->attributeOptionFactory->create();
                $option->setLabel($example);
                $option->setStoreLabels([$optionLabel]);
                $option->setSortOrder(0);
                $option->setIsDefault(false);

                $this->attributeOptionManagement->add(
                    Product::ENTITY,
                    $attributeId,
                    $option
                );
            }
        }
    }
}
