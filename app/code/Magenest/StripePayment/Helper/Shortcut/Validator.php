<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Helper\Shortcut;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
class Validator implements ValidatorInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private $productTypeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentData;

    protected $stripeConfig;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Payment\Helper\Data $paymentData,
        \Magenest\StripePayment\Helper\Config $stripeConfig
    ) {
        $this->registry = $registry;
        $this->productTypeConfig = $productTypeConfig;
        $this->paymentData = $paymentData;
        $this->stripeConfig = $stripeConfig;
    }

    /**
     * Validates shortcut
     *
     * @param  string $code
     * @param  bool   $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog)
    {
        return $this->isContextAvailable($code, $isInCatalog)
            && $this->isPriceOrSetAvailable($isInCatalog)
            && $this->isMethodAvailable($code);
    }

    /**
     * Checks visibility of context (cart or product page)
     *
     * @param  string $paymentCode Payment method code
     * @param  bool   $isInCatalog
     * @return bool
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        if ($isInCatalog) {
            if ($this->stripeConfig->getActiveOnProductDetail()) {
                return true;
            }
        } else {
            if ($this->stripeConfig->getActiveOnCart()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check is product available depending on final price or type set(configurable)
     *
     * @param  bool $isInCatalog
     * @return bool
     */
    public function isPriceOrSetAvailable($isInCatalog)
    {
        if ($isInCatalog) {
            // Show Stripe shortcut on a product view page only if product has nonzero price
            /** @var $currentProduct \Magento\Catalog\Model\Product */
            $currentProduct = $this->registry->registry('current_product');
            if ($currentProduct !== null) {
                $productPrice = (double)$currentProduct->getFinalPrice();
                $typeInstance = $currentProduct->getTypeInstance();
                if (empty($productPrice)
                    && !$this->productTypeConfig->isProductSet($currentProduct->getTypeId())
                    && !$typeInstance->canConfigure($currentProduct)
                ) {
                    return  false;
                }
            }
        }
        return true;
    }

    /**
     * Checks payment method and quote availability
     *
     * @param  string $paymentCode
     * @return bool
     */
    public function isMethodAvailable($paymentCode)
    {
        // check payment method availability
        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $this->paymentData->getMethodInstance($paymentCode);
        if (!$methodInstance->isAvailable()) {
            return false;
        }
        return true;
    }
}
