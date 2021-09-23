<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Controller\Paypal;

use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Amasty\StorePickupWithLocator\Model\Sales\AddressResolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\ShippingAddressManagementInterface;

/**
 * save shipping address for reset if need to change shipping method
 */
class SaveShippingAddress extends Action
{
    const DEFAULT_SHIPPING_ADDRESS = 'amasty_storepickup_default_shipping_address';

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var AddressResolver
     */
    private $addressResolver;

    public function __construct(
        Context $context,
        ShippingAddressManagementInterface $shippingAddressManagement,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        LocationResource $locationResource,
        LocationFactory $locationFactory,
        AddressResolver $addressResolver
    ) {
        parent::__construct($context);
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->locationResource = $locationResource;
        $this->locationFactory = $locationFactory;
        $this->addressResolver = $addressResolver;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $locationId = $this->_request->getParam('location_id');
        $cartId = $this->checkoutSession->getQuoteId();

        $quote = $this->quoteRepository->get($cartId);

        if ($quote->isVirtual()) {
            return $this->getResponse();
        }

        /** @var Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $stepData = $this->checkoutSession->getStepData('checkout', self::DEFAULT_SHIPPING_ADDRESS . '_' . $cartId);

        if (!$stepData) {
            $this->checkoutSession->setStepData(
                'checkout',
                self::DEFAULT_SHIPPING_ADDRESS . '_' . $cartId,
                $shippingAddress->getData()
            );
        }

        if ($locationId) {
            $this->addressResolver->setShippingInformation($quote, $locationId);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);

            $this->_view->loadLayout('paypal_express_review_details', true, true, false);

            return $this->getResponse()->setBody(
                $this->_view->getLayout()->getBlock('page.block')->setQuote($quote)->toHtml()
            );
        } elseif ($stepData) {
            $shippingAddress->setData($stepData);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }

        return $this->getResponse();
    }
}
