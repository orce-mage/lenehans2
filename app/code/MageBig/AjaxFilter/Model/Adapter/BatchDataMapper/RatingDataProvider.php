<?php

namespace MageBig\AjaxFilter\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

class RatingDataProvider implements AdditionalFieldsProviderInterface
{
    const FIELD_NAME = 'rating';

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];

        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        $product = $this->productFactory->create();
        foreach ($productIds as $productId) {
            $product->load($productId);
            $this->reviewFactory->create()->getEntitySummary($product, $storeId);
            $data = $product->getRatingSummary()->getRatingSummary();
            echo $data;
            $fields[$productId] = [self::FIELD_NAME => $data];
        }

        return $fields;
    }

}
