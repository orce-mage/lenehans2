<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Plugin;

class AddInfoToCartSection
{
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function afterGetSectionData(
        $subject,
        $result
    ) {
        $result['is_virtual'] = $this->checkoutSession->getQuote()->getIsVirtual();
        return $result;
    }
}
