<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\ObserverInterface;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class CouponEditObserver implements ObserverInterface
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\Coupon $coupon
    )
    {
        $this->helper = $helper;
        $this->config = $config;
        $this->coupon = $coupon;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isSubscriptionsEnabled())
            return;

        $data = $observer->getRequest()->getPostValue();

        if (!isset($data['rule_id']) || !is_numeric($data['rule_id']))
            return;

        if (empty($data['coupon_duration']))
            return;

        $this->coupon->load($data['rule_id'], 'rule_id');
        if (!$this->coupon->getId())
            $this->coupon->setRuleId($data['rule_id']);

        switch ($data['coupon_duration'])
        {
            case 'forever':
                if ($this->coupon->getId())
                    $this->coupon->delete();
                break;

            case 'once':
                $this->coupon->setCouponDuration('once');
                $this->coupon->setCouponMonths(0);
                $this->coupon->save();
                break;

            case 'repeating':
                if (!is_numeric($data['coupon_months']))
                    $this->helper->dieWithError(__("You have specified a coupon duration of Multiple Months, but you did not enter a valid months number."));

                $this->coupon->setCouponDuration('repeating');
                $this->coupon->setCouponMonths($data['coupon_months']);
                $this->coupon->save();
                break;

            default:

                break;
        }
    }
}
