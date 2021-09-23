<?php

namespace MageBig\WidgetPlus\Ui\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

class ProductData implements ProductRenderCollectorInterface
{
    const KEY = "form_key";

    /**
     * @var ProductRenderExtensionFactory
     */
    private $productRenderExtensionFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * FormKey constructor.
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        ProductRenderExtensionFactory $productRenderExtensionFactory,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->formKey = $formKey;
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $formKey = $this->formKey->getFormKey();
        $extensionAttributes->setFormKey($formKey);
        $extensionAttributes->setSku($product->getSku());

        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
