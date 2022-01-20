<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Helper;

class Constant
{
    const ADDITIONAL_PAYMENT_DATA = "additional_pay_data";
    const ADDITIONAL_TRANSACTION_ID = "transaction_id";
    const ADDITIONAL_TYPE = "additional_type";
    const ADDITIONAL_PAYMENT_ACTION = "additional_payment_action";
    const ADDITIONAL_THREEDS = "additional_threeds";
    const IFRAME_PAYMENT_TYPE_BITCOIN = "source_bitcoin";
    const IFRAME_PAYMENT_TYPE_CARD = "card";

    const SOURCE_ENDPOINT = "https://api.stripe.com/v1/sources";
    const CHARGE_ENDPOINT = "https://api.stripe.com/v1/charges";
    const REFUND_ENDPOINT = "https://api.stripe.com/v1/refunds";

    const RETRY_TIMEOUT = 3;
}
