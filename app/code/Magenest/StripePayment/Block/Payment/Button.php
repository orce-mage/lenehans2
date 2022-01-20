<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Payment;

use Magenest\StripePayment\Helper\Config;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Catalog\Block as CatalogBlock;
use Magenest\StripePayment\Helper\Shortcut\ValidatorInterface;

class Button extends \Magento\Framework\View\Element\Template implements ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     */
    private $_paymentMethodCode = '';

    /**
     * Shortcut alias
     *
     * @var string
     */
    private $_alias = '';

    /**
     * Start express action
     *
     * @var string
     */
    private $_startAction = '';

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $_paymentData;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $_mathRandom;

    /**
     * @var ValidatorInterface
     */
    private $_shortcutValidator;

    private $stripeConfig;

    private $stripeHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $alias
     * @param string $bmlMethodCode
     * @param string $shortcutTemplate
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Math\Random $mathRandom,
        ValidatorInterface $shortcutValidator,
        $paymentMethodCode,
        $alias,
        $shortcutTemplate,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        $this->_mathRandom = $mathRandom;
        $this->_shortcutValidator = $shortcutValidator;
        $this->stripeConfig = $stripeConfig;
        $this->stripeHelper = $stripeHelper;
        $this->_paymentMethodCode = $paymentMethodCode;
        $this->_alias = $alias;
        $this->setTemplate($shortcutTemplate);
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();
        $isInCatalog = $this->getIsInCatalogProduct();
        if (!$this->_shortcutValidator->validate($this->_paymentMethodCode, $isInCatalog)) {
            $this->_shouldRender = false;
            return $result;
        }

        $method = $this->_paymentData->getMethodInstance($this->_paymentMethodCode);
        if (!$method->isAvailable()) {
            $this->_shouldRender = false;
            return $result;
        }

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check is "OR" label position before shortcut
     *
     * @return bool
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    public function getStripeApplePayConfig()
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return [
            'button_label' => $this->stripeConfig->getApplepayButtonLabel()?$this->stripeConfig->getApplepayButtonLabel():"Total",
            'button_type' => $this->stripeConfig->getButtonType(),
            'button_theme' => $this->stripeConfig->getButtonTheme(),
            'publishableKey' => $this->stripeConfig->getPublishableKey(),
            'isZeroDecimal' => $this->stripeHelper->isZeroDecimal($currency)?true:false,
            'country_code' => $this->stripeConfig->getScopeConfig()->getValue('general/country/default'),
            'currency_code' => strtolower($currency)
        ];
    }
}
