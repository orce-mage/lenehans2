<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Helper\Shortcut;

class Factory
{
    /**
     * Default validator
     */
    const DEFAULT_VALIDATOR = \Magenest\StripePayment\Helper\Shortcut\Validator::class;

    /**
     * Checkout validator
     */
    const CHECKOUT_VALIDATOR = \Magenest\StripePayment\Helper\Shortcut\CheckoutValidator::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create($parameter = null)
    {
        $instanceName = self::DEFAULT_VALIDATOR;
        if (is_object($parameter) && $parameter instanceof \Magento\Checkout\Model\Session) {
            $instanceName = self::CHECKOUT_VALIDATOR;
        }
        return $this->_objectManager->create($instanceName);
    }
}
