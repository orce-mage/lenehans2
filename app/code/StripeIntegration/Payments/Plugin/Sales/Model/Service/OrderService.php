<?php

namespace StripeIntegration\Payments\Plugin\Sales\Model\Service;

class OrderService
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \StripeIntegration\Payments\Helper\Quote $quoteHelper,
        \StripeIntegration\Payments\Helper\GenericFactory $helperFactory
    ) {
        $this->rollback = $rollback;
        $this->quoteHelper = $quoteHelper;
        $this->helperFactory = $helperFactory;
    }

    public function aroundPlace($subject, \Closure $proceed, $order)
    {
        try
        {
            if (!empty($order) && !empty($order->getQuoteId()))
                $this->quoteHelper->quoteId = $order->getQuoteId();

            $this->rollback->reset();
            $returnValue = $proceed($order);
            $this->rollback->reset();

            return $returnValue;
        }
        catch (\Exception $e)
        {
            $helper = $this->helperFactory->create();
            $msg = $e->getMessage();

            if ($order->getId())
            {
                // The order has already been saved, so we don't want to run the rollback. The exception likely occurred in an order_save_after observer.
                $this->rollback->reset();
            }
            else
            {
                if (!$helper->isAuthenticationRequiredMessage($msg))
                    $this->rollback->run($e);
                else
                    $this->rollback->reset(); // In case some customization is trying to place multiple split-orders

            }

            if ($helper->isAuthenticationRequiredMessage($msg))
                throw $e;
            else
                $helper->dieWithError($e->getMessage(), $e);
        }
    }
}
