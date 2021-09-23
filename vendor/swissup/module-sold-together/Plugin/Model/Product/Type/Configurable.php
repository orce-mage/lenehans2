<?php

namespace Swissup\SoldTogether\Plugin\Model\Product\Type;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Subject;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class Configurable
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Fill buyRequest with attribute values for related products
     *
     * @param  Subject                       $subject
     * @param  \Magento\Framework\DataObject $buyRequest
     * @param  ProductInterface              $product
     * @return null
     */
    public function beforePrepareForCartAdvanced(
        Subject $subject,
        \Magento\Framework\DataObject $buyRequest,
        $product
    ) {
        $related = $this->request->getParam('related_product');
        $relatedIds = explode(',', $related);
        if (!$relatedIds || !in_array($product->getId(), $relatedIds)) {
            return null;
        }

        $relatedSuperAttribute = $this->request->getParam('related_product_super_attribute');
        if (!$relatedSuperAttribute) {
            return null;
        }

        $superAttribute = json_decode($relatedSuperAttribute, true);
        if (!$buyRequest->hasData('super_attribute')) {
            $buyRequest->setData('super_attribute', $superAttribute[$product->getId()] ?? []);
        }

        return null;
    }
}
