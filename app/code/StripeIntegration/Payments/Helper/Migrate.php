<?php

namespace StripeIntegration\Payments\Helper;

use Psr\Log\LoggerInterface;
use StripeIntegration\Payments\Helper\Logger;

class Migrate
{
    public $areaCode = null;

    public $methods = [
        // Old Stripe official module
        "stripecreditcards" => "stripe_payments",
        "stripesofort" => "stripe_payments_sofort",
        "stripebancontact" => "stripe_payments_bancontact",
        "stripealipay" => "stripe_payments_alipay",
        "stripegiropay" => "stripe_payments_giropay",
        "stripeideal" => "stripe_payments_ideal",
        "stripeinstantcheckout" => "stripe_payments",
        "stripeprzelewy" => "stripe_payments_p24",
        "stripesepa" => "stripe_payments_sepa",
        "stripesofort" => "stripe_payments_sofort",

        // Cryozonic modules
        "cryozonic_stripe" => "stripe_payments",
        "cryozonic_europayments_bancontact" => "stripe_payments_bancontact",
        "cryozonic_europayments_giropay" => "stripe_payments_giropay",
        "cryozonic_europayments_ideal" => "stripe_payments_ideal",
        "cryozonic_europayments_multibanco" => "stripe_payments_multibanco",
        "cryozonic_europayments_eps" => "stripe_payments_eps",
        "cryozonic_europayments_p24" => "stripe_payments_p24",
        "cryozonic_europayments_sepa" => "stripe_payments_sepa",
        "cryozonic_europayments_sofort" => "stripe_payments_sofort",
        "cryozonic_chinapayments_alipay" => "stripe_payments_alipay",
        "cryozonic_chinapayments_wechat" => "stripe_payments_wechat",

        // New Stripe Checkout
        "stripe_payments_checkout_card" => "stripe_payments_checkout"
    ];

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $paymentsCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Action $productAction
    )
    {
        $this->paymentsCollection = $paymentsCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->productAction = $productAction;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function initAreaCode()
    {
        if ($this->areaCode)
            return;

        $this->areaCode = $this->objectManager->get('StripeIntegration\Payments\Helper\AreaCode');
        $this->areaCode->setAreaCode();
    }

    public function orders()
    {
        $this->initAreaCode();
        $fromMethods = array_keys($this->methods);
        $collection = $this->paymentsCollection->addFieldToFilter("method", ["in" => $fromMethods]);
        // echo "\n";
        foreach ($collection as $entry)
        {
            $from = $entry->getMethod();
            $to = $this->methods[$from];
            // echo $entry->getEntityId() . ": $from => $to\n";
            $entry->setMethod($to);
            $entry->save();
        }
    }

    public function customers($setup)
    {
        $this->initAreaCode();
        $table = $setup->getTable('cryozonic_stripe_customers');
        if ($setup->tableExists('cryozonic_stripe_customers'))
        {
            $select = $setup->getConnection()->select()->from(['customers' => $setup->getTable('cryozonic_stripe_customers')]);
            $insertArray = [
                'id',
                'customer_id',
                'stripe_id',
                'last_retrieved',
                'customer_email',
                'session_id'
            ];
            $sqlQuery = $select->insertFromSelect(
                $setup->getTable('stripe_customers'),
                $insertArray,
                false
            );
            $setup->getConnection()->query($sqlQuery);
        }
    }

    public function subscriptions($setup)
    {
        $this->initAreaCode();
        $subscriptionProducts = $this->productCollectionFactory->create();

        try
        {
            $subscriptionProducts->addAttributeToSelect('*')
                ->addAttributeToFilter('cryozonic_sub_enabled', 1)
                ->load();

            foreach ($subscriptionProducts as $subscriptionProduct)
            {
                $this->productAction->updateAttributes([ $subscriptionProduct->getId() ], [
                    "stripe_sub_enabled" => $subscriptionProduct->getCryozonicSubEnabled(),
                    "stripe_sub_interval" => $subscriptionProduct->getCryozonicSubInterval(),
                    "stripe_sub_interval_count" => $subscriptionProduct->getCryozonicSubIntervalCount(),
                    "stripe_sub_trial" => $subscriptionProduct->getCryozonicSubTrial(),
                    "stripe_sub_initial_fee" => $subscriptionProduct->getCryozonicSubInitialFee()
                ], 0);
            }
        }
        catch (\Exception $e)
        {
            // The cryozonic_sub_enabled attribute does not exist
        }
    }

    public function adminConfigElementsToCheckout($setup)
    {
        $coreConfigData = $setup->getTable('core_config_data');

        $select = $setup->getConnection()->select()->from($coreConfigData)
            ->where('path like ?', 'payment/stripe_payments/%');

        $settings = $setup->getConnection()->fetchAll($select);
        foreach ($settings as $setting)
        {
            switch ($setting['path'])
            {
                case 'payment/stripe_payments/checkout_mode':
                    $setup->getConnection()->insert($coreConfigData,
                        [
                            'scope' => $setting['scope'],
                            'scope_id' => $setting['scope_id'],
                            'path' => 'payment/stripe_payments/payment_flow',
                            'value' => $setting['value'],
                        ]
                    );
                    break;

                case 'payment/stripe_payments/payment_action':
                case 'payment/stripe_payments/expired_authorizations':
                case 'payment/stripe_payments/automatic_invoicing':
                case 'payment/stripe_payments/card_icons':
                case 'payment/stripe_payments/card_icons_specific':
                case 'payment/stripe_payments/allowspecific':
                case 'payment/stripe_payments/specificcountry':
                case 'payment/stripe_payments/sort_order':
                    $newPath = str_replace("/stripe_payments/", "/stripe_payments_checkout/", $setting['path']);
                    $setup->getConnection()->insert($coreConfigData,
                        [
                            'scope' => $setting['scope'],
                            'scope_id' => $setting['scope_id'],
                            'path' => $newPath,
                            'value' => $setting['value'],
                        ]
                    );
                    break;

                case 'payment/stripe_payments/ccsave':
                    $newPath = "payment/stripe_payments_checkout/save_payment_method";
                    $newValue = min(1, $setting['value']);
                    $setup->getConnection()->insert($coreConfigData,
                        [
                            'scope' => $setting['scope'],
                            'scope_id' => $setting['scope_id'],
                            'path' => $newPath,
                            'value' => $newValue,
                        ]
                    );
                    break;
            }
        }
    }
}
