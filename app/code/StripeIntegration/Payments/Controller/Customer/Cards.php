<?php

namespace StripeIntegration\Payments\Controller\Customer;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;

class Cards extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

        $this->config = $config;
        $this->helper = $helper;
        $this->stripeCustomer = $helper->getCustomerModel();
        $this->customerSession = $session;

        if (!$session->isLoggedIn())
            $this->_redirect('customer/account/login');
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (isset($params['save']))
            return $this->saveCard($params);
        else if (isset($params['delete']))
            return $this->deleteCard($params['delete']);

        return $this->resultPageFactory->create();
    }

    public function saveCard($params)
    {
        try
        {
            if (empty($params['payment']) || empty($params['payment']['cc_stripejs_token']))
                throw new \Exception("Sorry, the card could not be saved. Unable to use Stripe.js.");

            $parts = explode(":", $params['payment']['cc_stripejs_token']);

            if (!$this->helper->isValidToken($parts[0]))
                throw new \Exception("Sorry, the card could not be saved. Unable to use Stripe.js.");

            try
            {
                $this->stripeCustomer->createStripeCustomerIfNotExists();
                $this->stripeCustomer->addCard($parts[0]);
                $this->helper->addSuccess(__("Card **** %1 was added successfully.", $parts[2]));
            }
            catch (\Exception $e)
            {
                $this->helper->logError($e->getMessage());
                $this->helper->addError("Could not add card: " . $e->getMessage());
            }
        }
        catch (\Stripe\Exception\CardException $e)
        {
            $this->helper->addError($e->getMessage());
        }
        catch (\Exception $e)
        {
            $this->helper->addError($e->getMessage());
            $this->helper->logError($e->getMessage());
            $this->helper->logError($e->getTraceAsString());
        }

        $this->_redirect('stripe/customer/cards');
    }

    public function deleteCard($token)
    {
        try
        {
            $customerId = $this->customerSession->getCustomer()->getId();
            $statuses = ['processing', 'fraud', 'pending_payment', 'payment_review', 'pending', 'holded'];
            $orders = $this->helper->getCustomerOrders($customerId, $statuses, $token);
            foreach ($orders as $order)
            {
                $message = __("Sorry, it is not possible to delete this card because order #%1 which was placed using this card is still being processed.", $order->getIncrementId());
                throw new LocalizedException($message);
            }

            $card = $this->stripeCustomer->deleteCard($token);

            // In case we deleted a source
            if (isset($card->card))
                $card = $card->card;

            $this->helper->addSuccess(__("Card **** %1 has been deleted.", $card->last4));
        }
        catch (LocalizedException $e)
        {
            $this->helper->addError($e->getMessage());
        }
        catch (\Stripe\Exception\CardException $e)
        {
            $this->helper->addError($e->getMessage());
        }
        catch (\Exception $e)
        {
            $this->helper->addError($e->getMessage());
            $this->helper->logError($e->getMessage());
            $this->helper->logError($e->getTraceAsString());
        }

        $this->_redirect('stripe/customer/cards');
    }
}
