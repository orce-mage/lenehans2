<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\LocalizedException;

class Ach extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_ach';

    protected $_code = self::METHOD_CODE;

    protected $type = 'ach';

    protected $_isInitializeNeeded      = false;
    protected $_canCapturePartial       = false;
    protected $_canAuthorize            = false;

    protected $_formBlockType           = 'StripeIntegration\Payments\Block\Ach\Method';

    protected $saveSourceOnCustomer = true;

    public function assignData(\Magento\Framework\DataObject $data)
    {
        if (empty($data['additional_data']['token']))
            $this->helper->dieWithError("An error has occurred while trying to verify your account details!");

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('token', $data['additional_data']['token']);

        $session = $this->checkoutHelper->getCheckout();
        $session->setStripePaymentsRedirectUrl(null);
        $session->setStripePaymentsClientSecret(null);

        return $this;
    }

    // If there is an already verified bank account for this token, return it and use it for the payment
    public function deduplicateBankAccounts($customer, $token)
    {
        $customerBankAccounts = $customer->sources->all(array('object' => 'bank_account'));
        $bankAccount = $token->bank_account;

        foreach ($customerBankAccounts->data as $item)
        {
            if ($item->fingerprint == $token->bank_account->fingerprint)
                return $item->id;
        }

        return $token->id;
    }


    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($this->helper->isAdmin())
        {
            throw new LocalizedException(__("Sorry, ACH payments cannot be invoiced or captured from the Magento admin. An paid invoice will automatically be created when the bank transfer is completed successfully."));
        }

        if ($amount > 0)
        {
            try
            {
                // Create the customer and add the bank account to the customer object
                $info = $this->getInfoInstance();
                $order = $info->getOrder();
                $this->customer->createStripeCustomerIfNotExists(false, $order);
                $customer = $this->customer->retrieveByStripeID();
                $payment->setAdditionalInformation('customer_stripe_id', $customer->id);

                $token = \Stripe\Token::retrieve($payment->getAdditionalInformation('token'));

                $sourceId = $this->deduplicateBankAccounts($customer, $token);

                if (strstr($sourceId, 'btok_') !== false)
                {
                    $source = $customer->sources->create(array('source' => $sourceId));
                    $payment->setAdditionalInformation('bank_account', $source->id);
                    $bankAccount = $source;
                }
                else // Will start with ba_
                {
                    $payment->setAdditionalInformation('bank_account', $sourceId);
                    $bankAccount = $customer->sources->retrieve($sourceId);
                }

                $order = $payment->getOrder();

                if ($bankAccount->status == "new")
                {
                    $verificationUrl = $this->urlBuilder->getUrl("stripe/ach/verification",
                        [
                            '_secure' => $this->request->isSecure(),
                            'customer' => $customer->id,
                            'account' => $bankAccount->id
                        ]
                    );

                    $comment = "Your order will remain pending until your bank account is verified. " .
                        "To verify your bank account, we have sent 2 micro-deposits which may take 1-2 business days to appear in your online statement. " .
                        "Once the deposits appear, please enter them at the verification page at %1 to complete your order. ";

                    $translatedComment = __($comment, $verificationUrl);
                    $order->setCustomerNoteNotify(true);
                    $order->setCustomerNote($translatedComment);
                    $payment->setIsTransactionPending(true);
                }
                else
                {
                    $comment = "The bank account used for this order has already been verified.";
                    $translatedComment = __($comment);
                    $order->addStatusHistoryComment($translatedComment);
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $helper = $objectManager->get('StripeIntegration\Payments\Helper\Ach');
                    $charge = $helper->charge($order);
                }

                $customer->save();
            }
            catch (\Exception $e)
            {
                if (strstr($e->getMessage(), 'Invalid country') !== false) {
                    throw new LocalizedException(__('Sorry, this payment method is not available in your country.'));
                }
                throw new LocalizedException(__($e->getMessage()));
            }
        }

        return parent::capture($payment, $amount);
    }
}
