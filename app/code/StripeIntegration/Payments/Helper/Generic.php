<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Session;
use StripeIntegration\Payments\Model;
use Psr\Log\LoggerInterface;
use Magento\Framework\Validator\Exception;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\ScopeInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;

class Generic
{
    public $magentoCustomerId = null;
    public $urlBuilder = null;
    protected $cards = [];
    public $orderComments = [];
    public $currentCustomer = null;
    public $productRepository = null;
    public $bundleProductOptions = [];
    public $orderPaymentIntents = [];
    public $quoteHasSubscriptions = [];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session\Quote $backendSessionQuote,
        \Magento\Framework\App\Request\Http $request,
        LoggerInterface $logger,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address $adminOrderAddressForm,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\Webapi\Response $apiResponse,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \StripeIntegration\Payments\Model\ResourceModel\StripeCustomer\Collection $customerCollection,
        \StripeIntegration\Payments\Helper\TaxHelper $taxHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        \StripeIntegration\Payments\Helper\ApiFactory $apiFactory,
        \StripeIntegration\Payments\Helper\Address $addressHelper,
        \StripeIntegration\Payments\Model\PaymentIntentFactory $paymentIntentFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \StripeIntegration\Payments\Model\CouponFactory $stripeCouponFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResultFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \StripeIntegration\Payments\Helper\Quote $quoteHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \StripeIntegration\Payments\Helper\SubscriptionQuote $subscriptionQuote,
        \Magento\Bundle\Model\OptionFactory $bundleOptionFactory,
        \Magento\Bundle\Model\Product\TypeFactory $bundleProductTypeFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->backendSessionQuote = $backendSessionQuote;
        $this->request = $request;
        $this->logger = $logger;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->invoiceService = $invoiceService;
        $this->creditmemo = $creditmemo;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->resource = $resource;
        $this->coreRegistry = $coreRegistry;
        $this->adminOrderAddressForm = $adminOrderAddressForm;
        $this->customerRegistry = $customerRegistry;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->cart = $cart;
        $this->invoiceCommentFactory = $invoiceCommentFactory;
        $this->customerAddress = $customerAddress;
        $this->apiResponse = $apiResponse;
        $this->transactionFactory = $transactionFactory;
        $this->requestInterface = $requestInterface;
        $this->urlBuilder = $urlBuilder;
        $this->pricingHelper = $pricingHelper;
        $this->cache = $cache;
        $this->encryptor = $encryptor;
        $this->userContext = $userContext;
        $this->orderSender = $orderSender;
        $this->priceCurrency = $priceCurrency;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->orderCommentSender = $orderCommentSender;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->invoiceManagement = $invoiceManagement;
        $this->customerCollection = $customerCollection;
        $this->taxHelper = $taxHelper;
        $this->productRepository = $productRepository;
        $this->imageFactory = $imageFactory;
        $this->apiFactory = $apiFactory;
        $this->addressHelper = $addressHelper;
        $this->paymentIntentFactory = $paymentIntentFactory;
        $this->quoteRepository = $quoteRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->couponFactory = $couponFactory;
        $this->stripeCouponFactory = $stripeCouponFactory;
        $this->checkoutHelper = $checkoutHelper;
        $this->ruleRepository = $ruleRepository;
        $this->invoiceSender = $invoiceSender;
        $this->quoteHelper = $quoteHelper;
        $this->taxConfig = $taxConfig;
        $this->transactionSearchResultFactory = $transactionSearchResultFactory;
        $this->subscriptionQuote = $subscriptionQuote;
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->bundleProductTypeFactory = $bundleProductTypeFactory;
        $this->currencyFactory = $currencyFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->sessionManager = $sessionManager;
    }

    public function getProductImage($product, $type = 'product_thumbnail_image')
    {
        return $this->imageFactory->create()
            ->init($product, $type)
            ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
            ->resize(380)
            ->getUrl();
    }

    public function getBackendSessionQuote()
    {
        return $this->backendSessionQuote->getQuote();
    }

    public function isSecure()
    {
        return $this->request->isSecure();
    }

    public function getSessionQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function getQuote($quoteId = null)
    {
        // Admin area new order page
        if ($this->isAdmin())
            return $this->getBackendSessionQuote();

        // Front end checkout
        $quote = $this->getSessionQuote();

        // API Request
        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
        {
            if ($quoteId)
                $quote = $this->quoteRepository->get($quoteId);
            else if ($this->quoteHelper->quoteId)
                $quote = $this->quoteRepository->get($this->quoteHelper->quoteId);
        }

        return $quote;
    }

    public function getStoreId()
    {
        if ($this->isAdmin())
        {
            if ($this->request->getParam('order_id', null))
            {
                // Viewing an order
                $order = $this->order->load($this->request->getParam('order_id', null));
                return $order->getStoreId();
            }
            if ($this->request->getParam('invoice_id', null))
            {
                // Viewing an invoice
                $invoice = $this->invoice->load($this->request->getParam('invoice_id', null));
                return $invoice->getStoreId();
            }
            else if ($this->request->getParam('creditmemo_id', null))
            {
                // Viewing a credit memo
                $creditmemo = $this->creditmemo->load($this->request->getParam('creditmemo_id', null));
                return $creditmemo->getStoreId();
            }
            else
            {
                // Creating a new order
                $quote = $this->getBackendSessionQuote();
                return $quote->getStoreId();
            }
        }
        else
        {
            return $this->storeManager->getStore()->getId();
        }
    }

    public function loadProductBySku($sku)
    {
        try
        {
            return $this->productRepository->get($sku);
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function loadProductById($productId)
    {
        if (!isset($this->products))
            $this->products = [];

        if (!empty($this->products[$productId]))
            return $this->products[$productId];

        $this->products[$productId] = $this->productFactory->create()->load($productId);

        return $this->products[$productId];
    }

    public function loadQuoteById($quoteId)
    {
        if (!isset($this->quotes))
            $this->quotes = [];

        if (!empty($this->quotes[$quoteId]))
            return $this->quotes[$quoteId];

        $this->quotes[$quoteId] = $this->quoteFactory->create()->load($quoteId);

        return $this->quotes[$quoteId];
    }

    public function loadOrderByIncrementId($incrementId)
    {
        if (empty($this->orders))
            $this->orders = [];

        if (!empty($this->orders[$incrementId]))
            return $this->orders[$incrementId];
        else
        {
            $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
            if ($order && $order->getId())
                $this->orders[$incrementId] = $order;
            else
                return $order;
        }

        return $this->orders[$incrementId];
    }

    public function loadOrderById($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }

    public function loadCustomerById($customerId)
    {
        return $this->customerRepositoryInterface->getById($customerId);
    }

    public function createInvoiceComment($msg, $notify = false, $visibleOnFront = false)
    {
        return $this->invoiceCommentFactory->create()
            ->setComment($msg)
            ->setIsCustomerNotified($notify)
            ->setIsVisibleOnFront($visibleOnFront);
    }

    public function isAdmin()
    {
        $areaCode = $this->appState->getAreaCode();

        return $areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    public function isAPIRequest()
    {
        $areaCode = $this->appState->getAreaCode();

        switch ($areaCode)
        {
            case 'webapi_rest': // \Magento\Framework\App\Area::AREA_WEBAPI_REST:
            case 'webapi_soap': // \Magento\Framework\App\Area::AREA_WEBAPI_SOAP:
            case 'graphql': // \Magento\Framework\App\Area::AREA_GRAPHQL: - Magento 2.1 doesn't have the constant
                return true;
            default:
                return false;
        }
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerId()
    {
        // If we are in the back office
        if ($this->isAdmin())
        {
            // About to refund/invoice an order
            if ($order = $this->coreRegistry->registry('current_order'))
                return $order->getCustomerId();

            // About to capture an invoice
            if ($invoice = $this->coreRegistry->registry('current_invoice'))
                return $invoice->getCustomerId();

            // Creating a new order from admin
            if ($this->adminOrderAddressForm && $this->adminOrderAddressForm->getCustomerId())
                return $this->adminOrderAddressForm->getCustomerId();
        }
        // If we are on the REST API
        else if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER)
        {
            return $this->userContext->getUserId();
        }
        // If we are on the checkout page
        else if ($this->customerSession->isLoggedIn())
        {
            return $this->customerSession->getCustomerId();
        }
        // A webhook has instantiated this object
        else if (!empty($this->magentoCustomerId))
        {
            return $this->magentoCustomerId;
        }

        return null;
    }

    public function getMagentoCustomer()
    {
        if ($this->customerSession->getCustomer()->getEntityId())
            return $this->customerSession->getCustomer();

        $customerId = $this->getCustomerId();
        if (!$customerId) return;

        $customer = $this->customerRegistry->retrieve($customerId);

        if ($customer->getEntityId())
            return $customer;

        return null;
    }

    public function isGuest()
    {
        return !$this->customerSession->isLoggedIn();
    }

    // Should return the email address of guest customers
    public function getCustomerEmail()
    {
        $customer = $this->getMagentoCustomer();

        if (!$customer)
            $customer = $this->getGuestCustomer();

        if (!$customer)
            return null;

        return trim(strtolower($customer->getEmail()));
    }

    public function getGuestCustomer($order = null)
    {
        if ($order)
        {
            return $this->getAddressFrom($order, 'billing');
        }
        else if (isset($this->_order))
        {
            return $this->getAddressFrom($this->_order, 'billing');
        }
        else
            return null;
    }

    public function getCustomerDefaultBillingAddress()
    {
        $customer = $this->getMagentoCustomer();
        if (!$customer) return null;

        $addressId = $customer->getDefaultBilling();
        if (!$addressId) return null;

        $this->customerAddress->clearInstance();
        $address = $this->customerAddress->load($addressId);
        return $address;
    }

    public function getCustomerBillingAddress()
    {
        $quote = $this->getSessionQuote();
        if (empty($quote))
            return null;

        return $quote->getBillingAddress();
    }

    public function getMultiCurrencyAmount($payment, $baseAmount)
    {
        $order = $payment->getOrder();
        $grandTotal = $order->getGrandTotal();
        $baseGrandTotal = $order->getBaseGrandTotal();

        $rate = $order->getBaseToOrderRate();
        if ($rate == 0) $rate = 1;

        // Full capture, ignore currency rate in case it changed
        if ($baseAmount == $baseGrandTotal)
            return $grandTotal;
        // Partial capture, consider currency rate but don't capture more than the original amount
        else if (is_numeric($rate))
            return min($baseAmount * $rate, $grandTotal);
        // Not a multicurrency capture
        else
            return $baseAmount;
    }

    public function getAddressFrom($order, $addressType = 'shipping')
    {
        if (!$order) return null;

        $addresses = $order->getAddresses();
        if (!empty($addresses))
        {
            foreach ($addresses as $address)
            {
                if ($address["address_type"] == $addressType)
                    return $address;
            }
        }
        else if ($addressType == "shipping" && $order->getShippingAddress() && $order->getShippingAddress()->getStreet(1))
        {
            return $order->getShippingAddress();
        }
        else if ($addressType == "billing" && $order->getBillingAddress() && $order->getBillingAddress()->getStreet(1))
        {
            return $order->getBillingAddress();
        }

        return null;
    }

    // Do not use Config::isSubscriptionsEnabled(), a circular dependency injection will appear
    public function isSubscriptionsEnabled()
    {
        $storeId = $this->getStoreId();

        $data = $this->scopeConfig->getValue("payment/stripe_payments_subscriptions/active", ScopeInterface::SCOPE_STORE, $storeId);

        return (bool)$data;
    }

    private function getProductOptionFor($item)
    {
        if (!$item->getParentItem())
            return null;

        $name = $item->getName();

        if ($productOptions = $item->getParentItem()->getProductOptions())
        {
            if (!empty($productOptions["bundle_options"]))
            {
                foreach ($productOptions["bundle_options"] as $bundleOption)
                {
                    if (!empty($bundleOption["value"]))
                    {
                        foreach ($bundleOption["value"] as $value)
                        {
                            if ($value["title"] == $name)
                            {
                                return $value;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    private function getSubscriptionQuoteItemFromBundle($item, $qty, $order)
    {
        $name = $item->getName();
        $productId = $item->getProductId();

        $parentQty = (($item->getParentItem() && $item->getParentItem()->getQty()) ? $item->getParentItem()->getQty() : 1);
        if ($item->getQty())
            $qty = $item->getQty() * $parentQty;

        if ($productOption = $this->getProductOptionFor($item)) // Order
        {
            if (!empty($productOption["price"]))
                $customPrice = $productOption["price"] / $parentQty;
            else
                $customPrice = $item->getPrice();

            // @todo: Report Magento bug between quote and order prices
            if ($order->getIncrementId())
                $newQuote = $this->subscriptionQuote->createNewQuoteFrom($order, $productId, $qty, null, $customPrice);
            else
                $newQuote = $this->subscriptionQuote->createNewQuoteFrom($order, $productId, $qty, $customPrice);

            foreach ($newQuote->getAllItems() as $newQuoteItem)
                return $newQuoteItem;
        }
        else if ($qtyOptions = $item->getParentItem()->getQtyOptions()) // Quote
        {
            $selections = $this->getBundleSelections($this->getStoreId(), $productId, $item->getParentItem()->getProduct());
            foreach ($qtyOptions as $qtyOption)
            {
                if ($qtyOption->getProductId() == $productId)
                {
                    $customPrice = $item->getProduct()->getPrice();
                    foreach ($selections as $selection)
                    {
                        if ($selection->getProductId() == $productId)
                        {
                            if ($selection->getSelectionPriceType() == 0) // 0 - fixed, 1 - percent
                            {
                                if ($selection->getSelectionPriceValue() && $selection->getSelectionPriceValue() > 0)
                                {
                                    $customPrice = $selection->getSelectionPriceValue();
                                }
                                else if ($selection->getPrice() && $selection->getPrice() > 0)
                                {
                                    $customPrice = $selection->getPrice();
                                }
                            }
                            else if ($selection->getSelectionPriceType() == 1)
                            {
                                $percent = $selection->getSelectionPriceValue();
                                // @todo - percent prices is not implemented
                                $this->dieWithError(__("Unsupported bundle subscription."));
                            }

                            break;
                        }
                    }

                    if ($order->getIncrementId())
                        $newQuote = $this->subscriptionQuote->createNewQuoteFrom($order, $productId, $qty, null, $customPrice);
                    else
                        $newQuote = $this->subscriptionQuote->createNewQuoteFrom($order, $productId, $qty, $customPrice);

                    foreach ($newQuote->getAllItems() as $newQuoteItem)
                        return $newQuoteItem;
                }
            }
        }

        $this->dieWithError(__("Unsupported bundle subscription."));
    }

    public function getBundleSelections($storeId, $productId, $product)
    {
        $options = $this->bundleOptionFactory->create()
            ->getResourceCollection()
            ->setProductIdFilter($productId)
            ->setPositionOrder();
        $options->joinValues($storeId);
        $typeInstance = $this->bundleProductTypeFactory->create();
        $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
        return $selections;
    }

    public function getItemQty($item)
    {
        $qty = max(/* quote */ $item->getQty(), /* order */ $item->getQtyOrdered());

        if ($item->getParentItem() && $item->getParentItem()->getProductType() == "configurable")
        {
            if (is_numeric($item->getParentItem()->getQty()))
                $qty *= $item->getParentItem()->getQty();
        }
        else if ($item->getParentItem() && $item->getParentItem()->getProductType() == "bundle")
        {
            if ($productOption = $this->getProductOptionFor($item))
            {
                if (!empty($productOption["qty"]))
                    $qty *= $productOption["qty"];
            }
        }

        return $qty;
    }

    public function getSubscriptionQuoteItemWithTotalsFrom($item, $order)
    {
        $qty = max(/* quote */ $item->getQty(), /* order */ $item->getQtyOrdered());

        if ($item->getParentItem() && $item->getParentItem()->getProductType() == "configurable")
        {
            return $item->getParentItem();
        }
        else if ($item->getParentItem() && $item->getParentItem()->getProductType() == "bundle")
        {
            return $this->getSubscriptionQuoteItemFromBundle($item, $qty, $order);
        }
        else
            return $item;
    }

    /**
     * Description
     * @param object $orderItem
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getSubscriptionProductFromOrderItem($item)
    {
        // Configurable products cannot be subscriptions. Also fixes a bug where if a configurable product
        // is added to the cart, and a bundled product already exists in the cart, Magento's core productModel->load()
        // method crashes with:
        // PHP Fatal error:  Uncaught Error: Call to undefined method Magento\Bundle\Model\Product\Type::getConfigurableAttributeCollection()
        if ($item->getProductType() == "configurable")
            return null;

        // The product for this order item has been deleted
        if (!$item->getProduct())
            return null;

        $product = $this->loadProductById($item->getProduct()->getEntityId());

        if ($product && $product->getStripeSubEnabled())
            return $product;

        return null;
    }

    public function isOrIncludesSubscription($orderItem)
    {
        $ids = $this->getSubscriptionIdsFromOrderItem($orderItem);
        return !empty($ids);
    }

    public function getSubscriptionProductsFromOrderItem($orderItem)
    {
        $products = [];
        $subscriptionProductIds = $this->getSubscriptionIdsFromOrderItem($orderItem);

        foreach ($subscriptionProductIds as $subscriptionProductId)
        {
            $products[$subscriptionProductId] = $this->loadProductById($subscriptionProductId);
        }

        return $products;
    }

    public function getSubscriptionIdsFromOrderItem($orderItem)
    {
        $ids = [];

        $type = $orderItem->getProductType();

        if ($type == "downloadable")
            return $ids;

        if (in_array($type, ["simple", "virtual"]))
        {
            $product = $this->loadProductById($orderItem->getProductId());
            if ($product->getStripeSubEnabled())
                return [ $orderItem->getProductId() ];
        }

        if ($type == "configurable")
        {
            foreach($orderItem->getChildrenItems() as $item)
            {
                $product = $this->loadProductById($item->getProductId());
                if ($product->getStripeSubEnabled())
                    $ids[] = $item->getProductId();
            }

            return $ids;
        }

        if ($type == "bundle")
        {
            $productIds = $this->getSelectedProductIdsFromBundleOrderItem($orderItem);

            foreach($productIds as $productId)
            {
                $product = $this->loadProductById($productId);
                if ($product->getStripeSubEnabled())
                    $ids[] = $productId;
            }

            return $ids;
        }

        return $ids;
    }

    public function getBundleProductOptionsData($productId)
    {
        if (!empty($this->bundleProductOptions[$productId]))
            return $this->bundleProductOptions[$productId];

        $product = $this->loadProductById($productId);

        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );

        foreach ($selectionCollection as $selection)
        {
            $selectionArray = [];
            $selectionArray['name'] = $selection->getName();
            $selectionArray['quantity'] = $selection->getSelectionQty();
            $selectionArray['price'] = $selection->getPrice();
            $selectionArray['product_id'] = $selection->getProductId();
            $productsArray[$selection->getOptionId()][$selection->getSelectionId()] = $selectionArray;
        }

        return $this->bundleProductOptions[$productId] = $productsArray;
    }

    public function getSelectedProductIdsFromBundleOrderItem($orderItem)
    {
        if ($orderItem->getProductType() != "bundle")
            return [];

        $productOptions = $orderItem->getProductOptions();
        if (empty($productOptions))
            return [];

        if (empty($productOptions["info_buyRequest"]["bundle_option"]))
            return [];

        $bundleOption = $productOptions["info_buyRequest"]["bundle_option"];

        $bundleData = $this->getBundleProductOptionsData($orderItem->getProductId());
        if (empty($bundleData))
            return [];

        $productIds = [];

        foreach ($bundleOption as $optionId => $option)
        {
            if (is_numeric($option) && !empty($bundleData[$optionId][$option]["product_id"]))
            {
                $productId = $bundleData[$optionId][$option]["product_id"];
                $productIds[$productId] = $productId;
            }
            else
            {
                foreach ($option as $selectionId => $selection)
                {
                    if (!empty($bundleData[$optionId][$selectionId]["product_id"]))
                    {
                        $productId = $bundleData[$optionId][$selectionId]["product_id"];
                        $productIds[$productId] = $productId;
                    }
                }
            }
        }

        return $productIds;
    }

    /**
     * Description
     * @param array<\Magento\Sales\Model\Order\Item> $items
     * @return bool
     */
    public function hasSubscriptionsIn($items, $returnSubscriptions = false)
    {
        if (!$this->isSubscriptionsEnabled())
            return false;

        if (empty($items))
            return false;

        foreach ($items as $item)
        {
            $product = $this->getSubscriptionProductFromOrderItem($item);
            if ($product)
                return true;
        }

        return false;
    }

    public function hasTrialSubscriptions($quote = null)
    {
        if (isset($this->_hasTrialSubscriptions) && $this->_hasTrialSubscriptions)
            return true;

        if (!$quote)
            $quote = $this->getQuote();

        $items = $quote->getAllItems();

        return $this->_hasTrialSubscriptions = $this->hasTrialSubscriptionsIn($items);
    }

    /**
     * Description
     * @param array<\Magento\Sales\Model\Order\Item> $items
     * @return bool
     */
    public function hasTrialSubscriptionsIn($items)
    {
        if (!$this->isSubscriptionsEnabled())
            return false;

        foreach ($items as $item)
        {
            $product = $this->getSubscriptionProductFromOrderItem($item);
            if (!$product)
                continue;

            $trial = $product->getStripeSubTrial();
            if (is_numeric($trial) && $trial > 0)
                return true;
            else
                continue;
        }

        return false;
    }

    public function hasOnlySubscriptionsIn($items)
    {
        if (!$this->isSubscriptionsEnabled())
            return false;

        foreach ($items as $item)
        {
            $product = $this->getSubscriptionProductFromOrderItem($item);
            if (!$product)
                continue;

            if ($product->getStripeSubEnabled())
                return true;
        }

        return false;
    }

    public function hasOnlyTrialSubscriptionsIn($items)
    {
        if (!$this->isSubscriptionsEnabled())
            return false;

        $found = false;

        foreach ($items as $item)
        {
            $product = $this->getSubscriptionProductFromOrderItem($item);
            if (!$product)
                continue;

            $trial = $product->getStripeSubTrial();
            if (is_numeric($trial) && $trial > 0)
                $found = true;
            else
                return false;
        }

        return $found;
    }

    public function hasSubscriptions($quote = null)
    {
        if (isset($this->_hasSubscriptions) && $this->_hasSubscriptions)
            return true;

        if ($quote)
            $items = $quote->getAllItems();
        else
            $items = $this->getQuote()->getAllItems();


        return $this->_hasSubscriptions = $this->hasSubscriptionsIn($items);
    }

    public function hasOnlyTrialSubscriptions($quote = null)
    {
        if (isset($this->_hasOnlyTrialSubscriptions) && $this->_hasOnlyTrialSubscriptions)
            return true;

        if ($quote)
            $items = $quote->getAllItems();
        else
            $items = $this->getQuote()->getAllItems();

        return $this->_hasOnlyTrialSubscriptions = $this->hasOnlyTrialSubscriptionsIn($items);
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), array(
            'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
            'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'));
    }

    public function isAuthorizationExpired($charge)
    {
        if (!$charge->refunded)
            return false;

        if (empty($charge->refunds->data[0]->reason))
            return false;

        if ($charge->refunds->data[0]->reason == "expired_uncaptured_charge")
            return true;

        return false;
    }

    public function addError($msg)
    {
        $this->messageManager->addError( __($msg) );
    }

    public function addSuccess($msg)
    {
        $this->messageManager->addSuccess( __($msg) );
    }

    public function logError($msg, $trace = null)
    {
        if (!$this->isAuthenticationRequiredMessage($msg))
        {
            \StripeIntegration\Payments\Helper\Logger::log(Config::module() . ": " . $msg);

            if ($trace)
                \StripeIntegration\Payments\Helper\Logger::log($trace);
        }
    }

    public function isStripeAPIKeyError($msg)
    {
        $pos1 = stripos($msg, "Invalid API key provided");
        $pos2 = stripos($msg, "No API key provided");
        if ($pos1 !== false || $pos2 !== false)
            return true;

        return false;
    }

    public function cleanError($msg)
    {
        if ($this->isStripeAPIKeyError($msg))
            return "Invalid Stripe API key provided.";

        return $msg;
    }

    public function isMultiShipping()
    {
        $quote = $this->getSessionQuote();
        if (empty($quote))
            return false;

        return $quote->getIsMultiShipping();
    }

    public function shouldLogExceptionTrace($e)
    {
        if (empty($e))
            return false;

        $msg = $e->getMessage();
        if ($this->isAuthenticationRequiredMessage($msg))
            return false;

        if (get_class($e) == \Stripe\Exception\CardException::class) // i.e. card declined, insufficient funds etc
            return false;

        if (get_class($e) == \Magento\Framework\Exception\CouldNotSaveException::class)
        {
            switch ($msg)
            {
                case "Your card was declined.":
                    return false;
                default:
                    break;
            }
        }

        return true;
    }

    public function dieWithError($msg, $e = null)
    {
        $this->logError($msg);

        if ($this->shouldLogExceptionTrace($e))
        {
            if ($e->getMessage() != $msg)
                $this->logError($e->getMessage());

            $this->logError($e->getTraceAsString());
        }

        if ($this->isAdmin())
            throw new CouldNotSaveException(__($msg));
        else if ($this->isAPIRequest())
            throw new CouldNotSaveException(__($this->cleanError($msg)), $e);
        else if ($this->isMultiShipping())
            throw new \Magento\Framework\Exception\LocalizedException(__($msg), $e);
        else
        {
            // We return in direct controller requests which already have their own error handlers
            // and during integration testing.
            $error = $this->cleanError($msg);
            $this->addError($error);
            return $error;
        }
    }

    public function maskException($e)
    {
        if (strpos($e->getMessage(), "Received unknown parameter: payment_method_options[card][moto]") === 0)
            $message = "You have enabled MOTO exemptions from the Stripe module configuration section, but your Stripe account has not been gated to use MOTO exemptions. Please contact support@stripe.com to request MOTO enabled for your Stripe account.";
        else
            $message = $e->getMessage();

        return $this->dieWithError($message, $e);
    }

    public function isValidToken($token)
    {
        if (!is_string($token))
            return false;

        if (!strlen($token))
            return false;

        if (strpos($token, "_") === FALSE)
            return false;

        return true;
    }

    public function captureOrder($order)
    {
        foreach($order->getInvoiceCollection() as $invoice)
        {
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->capture();
            $invoice->save();
        }
    }

    public function getInvoiceAmounts($invoice, $details)
    {
        $currency = strtolower($details['currency']);
        $cents = 100;
        if ($this->isZeroDecimal($currency))
            $cents = 1;
        $amount = ($details['amount'] / $cents);
        $baseAmount = round($amount / $invoice->getBaseToOrderRate(), 2);

        if (!empty($details["shipping"]))
        {
            $shipping = ($details['shipping'] / $cents);
            $baseShipping = round($shipping / $invoice->getBaseToOrderRate(), 2);
        }
        else
        {
            $shipping = 0;
            $baseShipping = 0;
        }

        if (!empty($details["tax"]))
        {
            $tax = ($details['tax'] / $cents);
            $baseTax = round($tax / $invoice->getBaseToOrderRate(), 2);
        }
        else
        {
            $tax = 0;
            $baseTax = 0;
        }

        return [
            "amount" => $amount,
            "base_amount" => $baseAmount,
            "shipping" => $shipping,
            "base_shipping" => $baseShipping,
            "tax" => $tax,
            "base_tax" => $baseTax
        ];
    }

    // Used for partial invoicing triggered from a partial Stripe dashboard capture
    public function adjustInvoiceAmounts(&$invoice, $details)
    {
        if (!is_array($details))
            return;

        $amounts = $this->getInvoiceAmounts($invoice, $details);
        $amount = $amounts['amount'];
        $baseAmount = $amounts['base_amount'];

        if ($invoice->getGrandTotal() != $amount)
        {
            if (!empty($amounts['shipping']))
                $invoice->setShippingAmount($amounts['shipping']);

            if (!empty($amounts['base_shipping']))
                $invoice->setBaseShippingAmount($amounts['base_shipping']);

            if (!empty($amounts['tax']))
                $invoice->setTaxAmount($amounts['tax']);

            if (!empty($amounts['base_tax']))
                $invoice->setBaseTaxAmount($amounts['base_tax']);

            $invoice->setGrandTotal($amount);
            $invoice->setBaseGrandTotal($baseAmount);

            $subtotal = 0;
            $baseSubtotal = 0;
            $items = $invoice->getAllItems();
            foreach ($items as $item)
            {
                $subtotal += $item->getRowTotal();
                $baseSubtotal += $item->getBaseRowTotal();
            }

            $invoice->setSubtotal($subtotal);
            $invoice->setBaseSubtotal($baseSubtotal);
        }
    }

    public function invoiceSubscriptionOrder($order, $transactionId = null, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE, $amount = null, $save = true)
    {
        if ($save)
            $dbTransaction = $this->transactionFactory->create();

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->setRequestedCaptureCase($captureCase);

        if ($transactionId)
        {
            $invoice->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);
        }

        $this->adjustInvoiceAmounts($invoice, $amount);

        $invoice->register();

        $comment = __("Captured payment of %1 through Stripe.", $order->formatPrice($invoice->getGrandTotal()));
        $order->addStatusToHistory($status = 'processing', $comment, $isCustomerNotified = false);

        if ($save)
            $dbTransaction->addObject($invoice)
                    ->addObject($order)
                    ->save();

        try
        {
            $this->invoiceSender->send($invoice);
        }
        catch (\Exception $e)
        {
            $this->logError($e->getMessage(), $e->getTraceAsString());
        }

        return $invoice;
    }

    public function invoiceOrder($order, $transactionId = null, $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE, $amount = null, $save = true)
    {
        if ($save)
            $dbTransaction = $this->transactionFactory->create();

        // This will kick in with "Authorize Only" mode orders, but not with "Authorize & Capture"
        if ($order->canInvoice())
        {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase($captureCase);

            if ($transactionId)
            {
                $invoice->setTransactionId($transactionId);
                $order->getPayment()->setLastTransId($transactionId);
            }

            $this->adjustInvoiceAmounts($invoice, $amount);

            $invoice->register();

            if ($save)
                $dbTransaction->addObject($invoice)
                        ->addObject($order)
                        ->save();

            return $invoice;
        }
        // Invoices have already been generated with either Authorize Only or Authorize & Capture, but have not actually been captured because
        // the source is not chargeable yet. These should have a pending status.
        else
        {
            foreach($order->getInvoiceCollection() as $invoice)
            {
                if ($invoice->canCapture())
                {
                    $invoice->setRequestedCaptureCase($captureCase);

                    $this->adjustInvoiceAmounts($invoice, $amount);

                    $invoice->pay();

                    if ($save)
                        $dbTransaction->addObject($invoice)
                                ->addObject($order)
                                ->save();

                    return $invoice;
                }
            }
        }

        return null;
    }

    // Pending orders are the ones that were placed with an asynchronous payment method, such as SOFORT or SEPA Direct Debit,
    // which may finalize the charge after several days or weeks
    public function invoicePendingOrder($order, $transactionId = null, $amount = null)
    {
        if (!$order->canInvoice())
            throw new \Exception("Order #" . $order->getIncrementId() . " cannot be invoiced.");

        $invoice = $this->invoiceService->prepareInvoice($order);

        if ($transactionId)
        {
            $captureCase = \Magento\Sales\Model\Order\Invoice::NOT_CAPTURE;
            $invoice->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);
        }
        else
        {
            $captureCase = \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE;
        }

        $invoice->setRequestedCaptureCase($captureCase);

        $this->adjustInvoiceAmounts($invoice, $amount);

        $invoice->register();

        $dbTransaction = $this->transactionFactory->create();

        $dbTransaction
            ->addObject($invoice)
            ->addObject($order)
            ->save();

        return $invoice;
    }

    public function invoiceNonSubscriptionItems($order, $save = false)
    {
        $payment = $order->getPayment();
        $paymentIntentId = $payment->getLastTransId();
        if (empty($paymentIntentId))
            return;

        $paymentIntent = \StripeIntegration\Payments\Model\Config::$stripeClient->paymentIntents->retrieve($paymentIntentId, []);
        if (empty($paymentIntent))
            return;

        $charge = $paymentIntent->charges->data[0];
        $items = $order->getAllItems();
        $orderItemQtys = [];
        foreach ($items as $item)
        {
            if (empty($item->getRowTotal()))
                continue; // This is a child of a configurable or bundle product which we should not attempt to invoice

            $orderItemId = null;
            if ($item->getParentItemId())
                $orderItemId = $item->getParentItemId(); // Configurable and bundled products
            else if ($item->getId())
                $orderItemId = $item->getId();

            if (is_numeric($orderItemId) && $orderItemId > 0)
            {
                if ($this->isOrIncludesSubscription($item))
                    $orderItemQtys[$orderItemId] = 0;
                else
                    $orderItemQtys[$orderItemId] = $item->getQtyOrdered();
            }

            if ($save)
            {
                $item->save();
            }
        }

        $invoice = $this->invoiceOrderItems($order, $orderItemQtys, $save);
        if ($invoice)
        {
            if (!$charge->captured)
                $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);

            $order->addRelatedObject($invoice);
        }

        if ($save)
        {
            $order->save();
            $invoice->save();
        }
    }

    public function invoiceTrialSubscriptionItems($order, $invoice, $save = true)
    {
        try
        {
            if (empty($invoice->lines->data))
                throw new LocalizedException(__("Error: The subscription invoice received from Stripe did not include any products."), 1);

            $productIds = [];
            $orderItemQtys = [];
            foreach ($invoice->lines->data as $invoiceItem)
            {
                if (!empty($invoiceItem->metadata->{"Product ID"}))
                    $productIds[] = $invoiceItem->metadata->{"Product ID"};
            }

            $orderItems = $order->getAllItems();
            $names = [];
            foreach ($orderItems as $orderItem)
            {
                $subscriptionIds = $this->getSubscriptionIdsFromOrderItem($orderItem);
                $id = $orderItem->getId();
                if (!empty(array_intersect($subscriptionIds, $productIds)))
                {
                    $orderItemQtys[$id] = $orderItem->getQtyOrdered();
                    $names[$id] = $orderItem->getName();
                }
                else
                {
                    $orderItemQtys[$id] = 0;
                }
            }

            if (!empty($orderItemQtys))
            {
                $order->setState("pending")->setStatus("pending");

                if ($save)
                    $order->save();

                $magentoInvoice = $this->invoiceOrderItems($order, $orderItemQtys, $save);
                if ($magentoInvoice)
                {
                    $comment = __("Trial subscription payments for this order are pending. Pending invoice(s) created for %1.", implode(", ", $names));
                    $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
                    $magentoInvoice->setTransactionId($invoice->subscription->id);

                    if ($save)
                        $magentoInvoice->save();
                }
            }

            $payment = $order->getPayment();
            $payment->setIsTransactionClosed(0);
            $payment->setIsFraudDetected(false);
        }
        catch (LocalizedException $e)
        {
            $order->addStatusToHistory($status = false, $e->getMessage(), $isCustomerNotified = false);
        }
    }

    public function getUnpaidTrialSubscriptionInvoiceFrom($order, $stripeInvoice)
    {
        if ($order->getBaseTotalDue() == 0)
            return null;

        if (empty($stripeInvoice->subscription->id))
            return null;


        foreach($order->getInvoiceCollection() as $invoice)
        {
            if (!$invoice->getTransactionId())
                continue;

            if ($invoice->getTransactionId() == $stripeInvoice->subscription->id)
            {
                return $invoice;
            }
        }

        return null;
    }

    public function payTrialSubscriptionInvoice($order, $invoice, $transactionId)
    {
        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        $invoice->pay();
        $invoice->save();

        $order->setCanSendNewEmailFlag(true);
        $this->notifyCustomer($order, __("Trial subscription period ended for order #%1. The subscription is now active.", $order->getIncrementId()));

        if ($order->getTotalDue() == 0)
            $order->setState("processing")->setStatus("processing");
        else
            $order->setState("pending")->setStatus("pending");

        $order->save();
    }

    public function cancelOrCloseOrder($order, $refundInvoices = false, $refundOffline = true)
    {
        $cancelled = false;

        $dbTransaction = $this->transactionFactory->create();

        // When in Authorize & Capture, uncaptured invoices exist, so we should cancel them first
        foreach($order->getInvoiceCollection() as $invoice)
        {
            if ($invoice->canCancel())
            {
                $invoice->cancel();
                $dbTransaction->addObject($invoice);
                $cancelled = true;
            }
            else if ($refundInvoices)
            {
                $creditmemo = $this->creditmemoFactory->createByOrder($order);
                $creditmemo->setInvoice($invoice);
                $this->creditmemoService->refund($creditmemo, $refundOffline);
                $cancelled = true;
            }
        }

        // When there are no invoices, the order can be canceled
        if ($order->canCancel())
        {
            $order->cancel();
            $dbTransaction->addObject($order);
            $cancelled = true;
        }

        $dbTransaction->save();
        $this->orderRepository->save($order);

        return $cancelled;
    }

    public function getSanitizedBillingInfo()
    {
        // This method is unnecessary in M2, the checkout passes the correct billing details
    }

    public function retrieveSource($token)
    {
        if (isset($this->sources[$token]))
            return $this->sources[$token];

        $this->sources[$token] = \Stripe\Source::retrieve($token);

        return $this->sources[$token];
    }

    public function maskError($msg)
    {
        if (stripos($msg, "You must verify a phone number on your Stripe account") === 0)
            return $msg;

        return false;
    }

    // Removes decorative strings that Magento adds to the transaction ID
    public function cleanToken($token)
    {
        return preg_replace('/-.*$/', '', $token);
    }

    public function retrieveCard($customer, $token)
    {
        if (isset($this->cards[$token]))
            return $this->cards[$token];

        $card = $customer->sources->retrieve($token);
        $this->cards[$token] = $card;

        return $card;
    }

    public function convertPaymentMethodToCard($paymentMethod)
    {
        if (!$paymentMethod || empty($paymentMethod->card))
            return null;

        $card = json_decode(json_encode($paymentMethod->card));
        $card->id = $paymentMethod->id;

        return $card;
    }

    public function cardType($code)
    {
        switch ($code) {
            case 'visa': return "Visa";
            case 'amex': return "American Express";
            case 'mastercard': return "MasterCard";
            case 'discover': return "Discover";
            case 'diners': return "Diners Club";
            case 'jcb': return "JCB";
            case 'unionpay': return "UnionPay";
            case 'cartes_bancaires': return "Cartes Bancaires";
            case null:
            case "":
                return "Card";
            default:
                return ucfirst($code);
        }
    }

    public function listCards($customer, $params = array())
    {
        try
        {
            $sources = $customer->sources;
            if (!empty($sources))
            {
                $cards = [];

                // Cards created through the Payment Methods API
                $data = \Stripe\PaymentMethod::all(['customer' => $customer->id, 'type' => 'card', 'limit' => 100]);
                foreach ($data->autoPagingIterator() as $pm)
                {
                    $cards[] = $this->convertPaymentMethodToCard($pm);
                }

                return $cards;
            }
            else
                return null;
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function findCard($customer, $last4, $expMonth, $expYear)
    {
        $cards = $this->listCards($customer);
        foreach ($cards as $card)
        {
            if ($last4 == $card->last4 &&
                $expMonth == $card->exp_month &&
                $expYear == $card->exp_year)
            {
                return $card;
            }
        }

        return false;
    }

    public function findCardByFingerprint($customer, $fingerprint)
    {
        $cards = $this->listCards($customer);
        foreach ($cards as $card)
        {
            if ($card->fingerprint == $fingerprint)
            {
                return $card;
            }
        }

        return false;
    }

    public function addSavedCard($customer, $newcard)
    {
        if (!$customer)
            return;

        if (!is_string($newcard))
            return null;

        // If we are adding a payment method, called from My Saved Cards section
        if (strpos($newcard, 'pm_') === 0)
        {
            $pm = \Stripe\PaymentMethod::retrieve($newcard);

            if (!isset($pm->card->fingerprint))
                return null;

            $pm->attach([ 'customer' => $customer->id ]);

            if (!empty($customer) && !empty($pm->id))
            {
                $this->deduplicatePaymentMethod(
                    $customer,
                    $pm->id,
                    $pm->type,
                    $pm->card->fingerprint,
                    \StripeIntegration\Payments\Model\Config::$stripeClient
                );
            }

            return $this->convertPaymentMethodToCard($pm);
        }
        // If we are adding a source
        else if (strpos($newcard, 'src_') === 0)
        {
            $source = $this->retrieveSource($newcard);
            // Card sources have been deprecated, we can only add Payment Method tokens pm_
            // if ($source->type == 'card')
            // {
            //     $card = $this->convertSourceToCard($source);
            // }
            if ($source->usage == 'reusable' && !isset($source->amount))
            {
                // SEPA Direct Debit with no amount set, no deduplication here
                $card = $customer->sources->create(array('source' => $source->id));
                $customer->default_source = $card->id;
                $customer->save();
                return $card;
            }
            else
            {
                // Bancontact, iDEAL etc cannot be added because they are not reusable
                return null;
            }

            if (isset($card->last4))
            {
                $last4 = $card->last4;
                $month = $card->exp_month;
                $year = $card->exp_year;
                $exists = $this->findCard($customer, $last4, $month, $year);
                if ($exists)
                {
                    $customer->default_source = $exists->id;
                    $customer->save();
                    return $exists;
                }
                else
                {
                    $card2 = $customer->sources->create(array('source' => $card->id));
                    $customer->default_source = $card2->id;
                    $customer->save();
                    return $card2;
                }
            }
        }

        return null;
    }

    public function formatStripePrice($price, $currency = null)
    {
        if (!$this->isZeroDecimal($currency))
            $price /= 100;

        return $this->priceCurrency->format($price, false, null, null, strtoupper($currency));
    }

    public function convertBaseAmountToStoreAmount($baseAmount)
    {
        $store = $this->storeManager->getStore();
        return $store->getBaseCurrency()->convert($baseAmount, $store->getCurrentCurrencyCode());
    }

    public function getUrl($path, $additionalParams = [])
    {
        $params = ['_secure' => $this->request->isSecure()];
        return $this->urlBuilder->getUrl($path, $params + $additionalParams);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function updateBillingAddress($token)
    {
        if (strpos($token, "pm_") === 0)
        {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($token);
            $quote = $this->getQuote();
            $magentoBillingDetails = $this->addressHelper->getStripeAddressFromMagentoAddress($quote->getBillingAddress());
            $paymentMethodBillingDetails = [
                "address" => [
                    "city" => $paymentMethod->billing_details->address->city,
                    "line1" => $paymentMethod->billing_details->address->line1,
                    "line2" => $paymentMethod->billing_details->address->line2,
                    "country" => $paymentMethod->billing_details->address->country,
                    "postal_code" => $paymentMethod->billing_details->address->postal_code,
                    "state" => $paymentMethod->billing_details->address->state
                ],
                "phone" => $paymentMethod->billing_details->phone,
                "name" => $paymentMethod->billing_details->name,
                "email" => $paymentMethod->billing_details->email
            ];
            if ($paymentMethodBillingDetails != $magentoBillingDetails || $paymentMethodBillingDetails["address"] != $magentoBillingDetails["address"])
            {
                \Stripe\PaymentMethod::update(
                  $paymentMethod->id,
                  ['billing_details' => $magentoBillingDetails]
                );
            }
        }
    }

    public function sendNewOrderEmailFor($order)
    {
        if (!$order->getEmailSent())
        {
            $order->setCanSendNewEmailFlag(true);
        }

        // Send the order email
        if ($order->getCanSendNewEmailFlag())
        {
            try
            {
                $this->orderSender->send($order);
            }
            catch (\Exception $e)
            {
                $this->logError($e->getMessage());
                $this->logError($e->getTraceAsString());
            }
        }
    }

    // An assumption is made that Webhooks->initStripeFrom($order) has already been called
    // to set the store and currency before the conversion, as the pricingHelper uses those
    public function getFormattedStripeAmount($amount, $currency, $order)
    {
        $orderAmount = $this->convertStripeAmountToOrderAmount($amount, $currency, $order);

        return $this->addCurrencySymbol($orderAmount, $currency);
    }

    public function convertMagentoAmountToStripeAmount($amount, $currency)
    {
        if (empty($amount) || !is_numeric($amount) || $amount < 0)
            return 0;

        $cents = 100;
        if ($this->isZeroDecimal($currency))
            $cents = 1;

        return round($amount * $cents);
    }

    public function convertOrderAmountToBaseAmount($amount, $currency, $order)
    {
        if (strtolower($currency) == strtolower($order->getOrderCurrencyCode()))
            $rate = $order->getBaseToOrderRate();
        else
            throw new \Exception("Currency code $currency was not used to place order #" . $order->getIncrementId());

        // $rate = $this->currencyFactory->create()->load($order->getBaseCurrencyCode())->getAnyRate($currency);
        if (empty($rate))
            return $amount; // The base currency and the order currency are the same

        return round($amount / $rate, 2);
    }

    public function convertStripeAmountToBaseOrderAmount($amount, $currency, $order)
    {
        if (strtolower($currency) != strtolower($order->getOrderCurrencyCode()))
            throw new \Exception("The order currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);
        $baseAmount = round($amount / $order->getBaseToOrderRate(), 2);

        return $baseAmount;
    }

    public function convertStripeAmountToBaseQuoteAmount($amount, $currency, $quote)
    {
        if (strtolower($currency) != strtolower($quote->getQuoteCurrencyCode()))
            throw new \Exception("The order currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);
        $baseAmount = round($amount / $quote->getBaseToQuoteRate(), 2);

        return $baseAmount;
    }

    public function convertStripeAmountToOrderAmount($amount, $currency, $order)
    {
        if (strtolower($currency) != strtolower($order->getOrderCurrencyCode()))
            throw new \Exception("The order currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);

        return $amount;
    }

    public function convertStripeAmountToQuoteAmount($amount, $currency, $quote)
    {
        if (strtolower($currency) != strtolower($quote->getQuoteCurrencyCode()))
            throw new \Exception("The quote currency does not match the Stripe currency");

        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);

        return $amount;
    }

    public function convertStripeAmountToMagentoAmount($amount, $currency)
    {
        $cents = 100;

        if ($this->isZeroDecimal($currency))
            $cents = 1;

        $amount = ($amount / $cents);

        return round($amount, 2);
    }

    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function addCurrencySymbol($amount, $currencyCode = null)
    {
        if (empty($currencyCode))
            $currencyCode = $this->getCurrentCurrencyCode();

        return $this->priceCurrency->format($amount, false, null, null, strtoupper($currencyCode));
    }

    public function getSubscriptionProductIdFrom($quoteItem)
    {
        $type = $quoteItem->getProductType();
        switch ($type) {
            case 'configurable':
                foreach ($quoteItem->getQtyOptions() as $key => $child)
                    return $key;
            default:
                return $quoteItem->getProductId();
        }
    }

    public function getSubscriptionProductFromQuoteItem($quoteItem)
    {
        $productId = $this->getSubscriptionProductIdFrom($quoteItem);

        if (empty($productId))
            return null;

        $product = $this->loadProductById($productId);

        if (!$product->getStripeSubEnabled())
            return null;

        return $product;
    }

    public function getClearSourceInfo($data)
    {
        $info = [];
        $remove = ['mandate_url', 'fingerprint', 'client_token', 'data_string'];
        foreach ($data as $key => $value)
        {
            if (!in_array($key, $remove))
                $info[$key] = $value;
        }

        // Remove Klarna pay fields
        $startsWith = ["pay_"];
        foreach ($info as $key => $value)
        {
            foreach ($startsWith as $part)
            {
                if (strpos($key, $part) === 0)
                    unset($info[$key]);
            }
        }

        return $info;
    }

    public function notifyCustomer($order, $comment)
    {
        $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
        $order->setCustomerNote($comment);
        // $order->save();
        $this->orderCommentSender->send($order, $notify = true, $comment);
    }

    public function sendNewOrderEmailWithComment($order, $comment)
    {
        $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
        $this->orderComments[$order->getIncrementId()] = $comment;
        $order->setEmailSent(false);
        $this->orderSender->send($order, true);
    }

    public function isAuthenticationRequiredMessage($message)
    {
        return (strpos($message, "Authentication Required: ") !== false);
    }

    public function getOrderDescription($order)
    {
        if ($order->getCustomerIsGuest())
        {
            $customer = $this->getGuestCustomer($order);
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        }
        else
            $customerName = $order->getCustomerName();

        if ($this->hasSubscriptionsIn($order->getAllItems()))
            $subscription = "subscription ";
        else
            $subscription = "";

        if ($this->isMultiShipping())
            $description = "Multi-shipping {$subscription}order #" . $order->getRealOrderId() . " by $customerName";
        else
            $description = "{$subscription}order #" . $order->getRealOrderId() . " by $customerName";

        return ucfirst($description);
    }

    public function supportsSubscriptions(?string $method)
    {
        if (empty($method))
            return false;

        return in_array($method, ["stripe_payments", "stripe_payments_checkout", "stripe_payments_express"]);
    }

    public function isStripeCheckoutMethod(?string $method)
    {
        if (empty($method))
            return false;

        return in_array($method, ["stripe_payments_checkout"]);
    }

    public function getLevel3DataFrom($order, $useStoreCurrency)
    {
        if (empty($order))
            return null;

        $merchantReference = $order->getIncrementId();

        if (empty($merchantReference))
            return null;

        if ($useStoreCurrency)
            $currency = $order->getOrderCurrencyCode();
        else
            $currency = $order->getBaseCurrencyCode();

        $cents = $this->isZeroDecimal($currency) ? 1 : 100;

        $data = [
            "merchant_reference" => $merchantReference,
            "line_items" => $this->getLevel3DataLineItemsFrom($order, $useStoreCurrency, $cents)
        ];

        if (!$order->getIsVirtual())
        {
            $data["shipping_address_zip"] = $order->getShippingAddress()->getPostcode();

            if ($useStoreCurrency)
                $data["shipping_amount"] = round($order->getShippingInclTax() * $cents);
            else
                $data["shipping_amount"] = round($order->getBaseShippingInclTax() * $cents);
        }

        $data = array_merge($data, $this->getLevel3AdditionalDataFrom($order, $useStoreCurrency, $cents));

        return $data;
    }

    public function getLevel3DataLineItemsFrom($order, $useStoreCurrency, $cents)
    {
        $items = [];

        $quoteItems = $order->getAllVisibleItems();
        foreach ($quoteItems as $item)
        {
            if ($useStoreCurrency)
            {
                $amount = $item->getPrice();
                $tax = round($item->getTaxAmount() * $cents);
                $discount = round($item->getDiscountAmount() * $cents);
            }
            else
            {
                $amount = $item->getBasePrice();
                $tax = round($item->getBaseTaxAmount() * $cents);
                $discount = round($item->getBaseDiscountAmount() * $cents);
            }

            $items[] = [
                "product_code" => substr($item->getSku(), 0, 12),
                "product_description" => substr($item->getName(), 0, 26),
                "unit_cost" => round($amount * $cents),
                "quantity" => $item->getQtyOrdered(),
                "tax_amount" => $tax,
                "discount_amount" => $discount
            ];
        }

        return $items;
    }

    public function getLevel3AdditionalDataFrom($order, $useStoreCurrency, $cents)
    {
        // You can overwrite to add the shipping_from_zip or customer_reference parameters here
        return [];
    }

    public function getCustomerModel()
    {
        if ($this->currentCustomer)
            return $this->currentCustomer;

        $pk = $this->getPublishableKey();
        if (empty($pk))
            return $this->currentCustomer = \Magento\Framework\App\ObjectManager::getInstance()->create('StripeIntegration\Payments\Model\StripeCustomer');

        $customerId = $this->getCustomerId();
        $model = null;

        if (is_numeric($customerId) && $customerId > 0)
        {
            $model = $this->customerCollection->getByCustomerId($customerId, $pk);
            if ($model && $model->getId())
            {
                $model->updateSessionId();
                $this->currentCustomer = $model;
            }
        }
        else if (!$this->isAdmin())
        {
            $stripeCustomerId = $this->sessionManager->getStripeCustomerId();
            $model = null;

            if ($stripeCustomerId)
            {
                $model = $this->customerCollection->getByStripeCustomerIdAndPk($stripeCustomerId, $pk);
            }
            else
            {
                $sessionId = $this->sessionManager->getSessionId();
                $model = $this->customerCollection->getBySessionId($sessionId, $pk);
            }

            if ($model && $model->getId())
                $this->currentCustomer = $model;
        }

        if (!$this->currentCustomer)
            $this->currentCustomer = \Magento\Framework\App\ObjectManager::getInstance()->create('StripeIntegration\Payments\Model\StripeCustomer');

        return $this->currentCustomer;
    }

    public function getCustomerModelByStripeId($stripeId)
    {
        return $this->customerCollection->getByStripeCustomerId($stripeId);
    }

    public function getPublishableKey()
    {
        $storeId = $this->getStoreId();
        $mode = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_mode", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $pk = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_pk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return trim($pk);
    }

    public function getStripeUrl($liveMode, $objectType, $id)
    {
        if ($liveMode)
            return "https://dashboard.stripe.com/$objectType/$id";
        else
            return "https://dashboard.stripe.com/test/$objectType/$id";
    }

    public function holdOrder($order)
    {
        $order->setHoldBeforeState($order->getState());
        $order->setHoldBeforeStatus($order->getStatus());
        $order->setState(\Magento\Sales\Model\Order::STATE_HOLDED)
            ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_HOLDED));
        $comment = __("Order placed under manual review by Stripe Radar.");
        $order->addStatusToHistory(false, $comment, false);

        $pi = $order->getPayment()->getLastTransId();
        if (!empty($pi))
        {
            $paymentIntent = $this->paymentIntentFactory->create();
            $paymentIntent->load($pi, 'pi_id'); // Finds or creates the row
            $paymentIntent->setPiId($pi);
            $paymentIntent->setOrderIncrementId($order->getIncrementId());
            $paymentIntent->setQuoteId($order->getQuoteId());
            $paymentIntent->save();
        }

        return $order;
    }

    public function addWarning($msg)
    {
        if ($this->isAdmin())
            $this->messageManager->addWarning($msg);
    }

    public function addOrderComment($msg, $order, $isCustomerNotified = false)
    {
        if ($order)
            $order->addCommentToStatusHistory($msg);
    }

    public function capture($token, $payment, $amount, $useSavedCard = false)
    {
        $token = $this->cleanToken($token);
        $order = $payment->getOrder();

        if ($token == "cannot_capture_subscriptions")
        {
            $msg = __("Subscription items cannot be captured online. Will capture offline instead.");
            $this->addWarning($msg);
            $this->addOrderComment($msg, $order);
            return;
        }

        try
        {
            if (strpos($token, 'pi_') === 0)
            {
                $pi = \Stripe\PaymentIntent::retrieve($token);
                $ch = $pi->charges->data[0];
                $paymentObject = $pi;
                $amountToCapture = "amount_to_capture";
            }
            else
            {
                $ch = \Stripe\Charge::retrieve($token);
                $paymentObject = $ch;
                $amountToCapture = "amount";
            }

            $currency = $ch->currency;

            if ($currency == strtolower($order->getOrderCurrencyCode()))
                $finalAmount = $this->getMultiCurrencyAmount($payment, $amount);
            else if ($currency == strtolower($order->getBaseCurrencyCode()))
                $finalAmount = $amount;
            else
                $this->dieWithError("Cannot capture payment because it was created using a different currency ({$ch->currency}).");

            $cents = 100;
            if ($this->isZeroDecimal($currency))
                $cents = 1;

            $stripeAmount = round($finalAmount * $cents);

            if ($this->isAuthorizationExpired($ch))
            {
                if ($useSavedCard)
                    $this->apiFactory->create()->reCreateCharge($payment, $amount, $ch);
                else
                    throw new \Exception("The payment authorization with the customer's bank has expired. If you wish to create a new payment using a saved card, please enable Expired Authorizations from Configuration &rarr; Sales &rarr; Payment Methods &rarr; Stripe &rarr; Card Payments &rarr; Expired Authorizations.");
            }
            else if ($ch->refunded)
            {
                $this->dieWithError("This amount for this invoice has been refunded in Stripe.");
            }
            else if ($ch->captured)
            {
                if ($order->getInvoiceCollection()->getSize() > 0)
                {
                    foreach ($order->getInvoiceCollection() as $invoice)
                    {
                        if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_PAID)
                            throw new \Exception("Multiple partial payment captures are not supported by Stripe. Please create an offline Credit Memo instead.");
                    }
                }
                $capturedAmount = $ch->amount - $ch->amount_refunded;
                $humanReadableAmount = $this->formatStripePrice($stripeAmount - $capturedAmount, $ch->currency);
                if ($this->hasTrialSubscriptionsIn($order->getAllItems()))
                    $msg = __("%1 could not be captured online because this cart includes subscriptions which are trialing. Capturing %1 offline instead.", $humanReadableAmount);
                else
                    $msg = __("%1 could not be captured online because it was already captured via Stripe. Capturing %1 offline instead.", $humanReadableAmount);

                $this->addWarning($msg);
                $this->addOrderComment($msg, $order);
            }
            else // status == pending
            {
                $availableAmount = $ch->amount;
                if ($availableAmount < $stripeAmount)
                {
                    $available = $this->formatStripePrice($availableAmount, $ch->currency);
                    $requested = $this->formatStripePrice($stripeAmount, $ch->currency);

                    if ($this->hasSubscriptionsIn($order->getAllItems()))
                        $msg = __("Capturing %1 instead of %2 because subscription items cannot be captured.", $available, $requested);
                    else
                        $msg = __("The maximum available amount to capture is %1, but a capture of %2 was requested. Will capture %1 instead.", $available, $requested);

                    $this->addWarning($msg);
                    $this->addOrderComment($msg, $order);
                    $stripeAmount = $availableAmount;
                }

                $this->cache->save($value = "1", $key = "admin_captured_" . $paymentObject->id, ["stripe_payments"], $lifetime = 60 * 60);
                $paymentObject->capture(array($amountToCapture => $stripeAmount));
            }
        }
        catch (\Exception $e)
        {
            $this->logger->critical($e->getMessage());
            $this->dieWithError($e->getMessage(), $e);
        }
    }

    public function deduplicatePaymentMethod($customerId, $paymentMethodId, $paymentMethodType, $fingerprint, $stripeClient)
    {
        if ($paymentMethodType != "card" || empty($fingerprint) || empty($customerId) || empty($paymentMethodId))
            return;

        try
        {

            switch ($paymentMethodType)
            {
                case "card":

                    $subscriptions = [];
                    $data = $stripeClient->subscriptions->all(['limit' => 100, 'customer' => $customerId]);
                    foreach ($data->autoPagingIterator() as $subscription)
                        $subscriptions[] = $subscription;

                    $collection = $stripeClient->paymentMethods->all([
                      'customer' => $customerId,
                      'type' => $paymentMethodType
                    ]);

                    foreach ($collection->data as $object)
                    {
                        if ($object['id'] == $paymentMethodId || $object['card']['fingerprint'] != $fingerprint)
                            continue;

                        // Update subscriptions which use the card that will be deleted
                        foreach ($subscriptions as $subscription)
                        {
                            if ($subscription->default_payment_method == $object['id'])
                            {
                                try
                                {
                                    $stripeClient->subscriptions->update($subscription->id, ['default_payment_method' => $paymentMethodId]);
                                }
                                catch (\Exception $e)
                                {
                                    $this->logError($e->getMessage());
                                    $this->logError($e->getTraceAsString());
                                }
                            }
                        }

                        // Detach the card from the customer
                        try
                        {
                            $stripeClient->paymentMethods->detach($object['id']);
                        }
                        catch (\Exception $e)
                        {
                            $this->logError($e->getMessage());
                            $this->logError($e->getTraceAsString());
                        }
                    }

                    break;

                default:

                    break;
            }
        }
        catch (\Exception $e)
        {
            $this->logError($e->getMessage());
            $this->logError($e->getTraceAsString());
        }
    }

    public function getPRAPIMethodType()
    {
        if (empty($_SERVER['HTTP_USER_AGENT']))
            return null;

        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($userAgent, 'chrome') !== false)
            return 'Google Pay';

        if (strpos($userAgent, 'safari') !== false)
            return 'Apple Pay';

        if (strpos($userAgent, 'edge') !== false)
            return 'Microsoft Pay';

        if (strpos($userAgent, 'opera') !== false)
            return 'Opera Browser Wallet';

        if (strpos($userAgent, 'firefox') !== false)
            return 'Firefox Browser Wallet';

        if (strpos($userAgent, 'samsung') !== false)
            return 'Samsung Browser Wallet';

        if (strpos($userAgent, 'qqbrowser') !== false)
            return 'QQ Browser Wallet';

        return null;
    }

    public function getPaymentLocation($location)
    {
        if (stripos($location, 'product') === 0)
            return "Product Page";

        switch ($location) {
            case 'cart':
                return "Shopping Cart Page";

            case 'checkout':
                return "Checkout Page";

            case 'minicart':
                return "Mini cart";

            default:
                return "Unknown";
        }
    }

    public function getCustomerOrders($customerId, $statuses = [], $paymentMethodId = null)
    {
        $collection = $this->orderCollectionFactory->create($customerId)
            ->addAttributeToSelect('*')
            ->join(
                ['pi' => $this->resource->getConnection()->getTableName('stripe_payment_intents')],
                'main_table.customer_id = pi.customer_id and main_table.increment_id = pi.order_increment_id',
                []
            )
            ->setOrder(
                'created_at',
                'desc'
            );

        if (!empty($statuses))
            $collection->addFieldToFilter('main_table.status', ['in' => $statuses]);

        if (!empty($paymentMethodId))
            $collection->addFieldToFilter('pi.pm_id', ['eq' => $paymentMethodId]);

        return $collection;
    }

    public function loadCouponByCouponCode($couponCode)
    {
        return $this->couponFactory->create()->loadByCode($couponCode);
    }

    public function loadRuleByRuleId($ruleId)
    {
        return $this->ruleRepository->getById($ruleId);
    }

    public function loadStripeCouponByRuleId($ruleId)
    {
        return $this->stripeCouponFactory->create()->load($ruleId, 'rule_id');
    }

    public function refundPaymentIntent($payment, $amount, $currency)
    {
        $paymentIntentId = $payment->getLastTransId();
        $paymentIntentId = preg_replace('/-.*$/', '', $paymentIntentId);

        // Redirect-based payment method where an invoice is in Pending status, with no transaction ID
        if (empty($paymentIntentId))
            return; // Creates an Offline Credit Memo

        if (strpos($paymentIntentId, 'pi_') !== 0)
            throw new LocalizedException(__("Could not refund invoice because %1 is not a valid Payment Intent ID", $paymentIntentId));

        $params = ["amount" => $this->convertMagentoAmountToStripeAmount($amount, $currency)];

        $pi = \Stripe\PaymentIntent::retrieve($paymentIntentId);

        if ($pi->status == \StripeIntegration\Payments\Model\PaymentIntent::AUTHORIZED)
        {
            $pi->cancel();
            return;
        }
        else
        {
            $charge = $pi->charges->data[0];
            $params["charge"] = $charge->id;
        }

        if (!$charge->refunded) // This is true when an authorization has expired or when there was a refund through the Stripe account
        {
            $this->cache->save($value = "1", $key = "admin_refunded_" . $charge->id, ["stripe_payments"], $lifetime = 60 * 60);
            \Stripe\Refund::create($params);
        }
        else
        {
            $comment = __('An attempt to manually refund the order was made, however this order was already refunded in Stripe. Creating an offline refund instead.');
            $payment->getOrder()->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
        }
    }

    public function setQuoteTaxFrom($stripeTaxAmount, $stripeCurrency, $quote)
    {
        // Stripe uses a different tax rounding algorithm than Magento, so check for tax rounding errors and fix them
        $tax = $this->convertStripeAmountToQuoteAmount($stripeTaxAmount, $stripeCurrency, $quote);
        $baseTax = $this->convertStripeAmountToBaseQuoteAmount($stripeTaxAmount, $stripeCurrency, $quote);
        $taxDiff = $tax - $quote->getTaxAmount();
        $baseTaxDiff = $baseTax - $quote->getBaseTaxAmount();
        $quote->setTaxAmount($tax);
        $quote->setBaseTaxAmount($baseTax);
        $quote->setGrandTotal($quote->getGrandTotal() + $taxDiff);
        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $baseTaxDiff);
    }

    public function sendPaymentFailedEmail($msg)
    {
        try
        {
            $this->checkoutHelper->sendPaymentFailedEmail($this->getQuote(), $msg);
        }
        catch (\Exception $e)
        {
            $this->logError($e->getMessage(), $e->getTraceAsString());
        }
    }

    public function isRecurringOrder($method)
    {
        try
        {
            $info = $method->getInfoInstance();

            if (!$info)
                return false;

            return $info->getAdditionalInformation("is_recurring_subscription");
        }
        catch (\Exception $e)
        {
            return false;
        }

        return false;
    }

    public function resetPaymentData($payment)
    {
        // Reset a previously initialized 3D Secure session
        $payment->setAdditionalInformation('stripejs_token', null)
            ->setAdditionalInformation('save_card', null)
            ->setAdditionalInformation('token', null)
            ->setAdditionalInformation("is_recurring_subscription", null)
            ->setAdditionalInformation("is_migrated_subscription", null)
            ->setAdditionalInformation("subscription_customer", null)
            ->setAdditionalInformation("subscription_start", null)
            ->setAdditionalInformation("remove_initial_fee", null)
            ->setAdditionalInformation("off_session", null)
            ->setAdditionalInformation("use_store_currency", null)
            ->setAdditionalInformation("selected_plan", null);
    }

    public function setPaymentData($payment, $token, $saveCard, $useStoreCurrency, $selectedPlan)
    {
        if (!$this->isValidToken($token))
            $this->dieWithError("Sorry, we could not perform a card security check. Please contact us to complete your purchase.");

        $this->resetPaymentData($payment);

        $payment->setAdditionalInformation('use_store_currency', $useStoreCurrency);
        $payment->setAdditionalInformation('token', $token);
        $payment->setAdditionalInformation('save_card', $saveCard);

        if (is_numeric($selectedPlan))
            $payment->setAdditionalInformation('selected_plan', $selectedPlan);
    }

    public function assignPaymentData($payment, $data, $useStoreCurrency)
    {
        // If using a saved card
        if (!empty($data['cc_saved']) && $data['cc_saved'] != 'new_card')
        {
            $card = explode(':', $data['cc_saved']);

            $this->setPaymentData($payment, $card[0], $data["cc_save"], $useStoreCurrency, $data["selected_plan"]);

            $this->updateBillingAddress($card[0]);

            return;
        }

        // Scenarios by OSC modules trying to prematurely save payment details
        if (empty($data['cc_stripejs_token']))
            return;

        $card = explode(':', $data['cc_stripejs_token']);
        $data['cc_stripejs_token'] = $card[0]; // To be used by Stripe Subscriptions

        $this->setPaymentData($payment, $card[0], $data["cc_save"], $useStoreCurrency, $data["selected_plan"]);
    }

    public function shippingIncludesTax($store = null)
    {
        return $this->taxConfig->shippingPriceIncludesTax($store);
    }

    public function priceIncludesTax($store = null)
    {
        return $this->taxConfig->priceIncludesTax($store);
    }

    /**
     * Transaction interface types
     * const TYPE_PAYMENT = 'payment';
     * const TYPE_ORDER = 'order';
     * const TYPE_AUTH = 'authorization';
     * const TYPE_CAPTURE = 'capture';
     * const TYPE_VOID = 'void';
     * const TYPE_REFUND = 'refund';
     **/
    public function addTransaction($order, $transactionId, $transactionType = "capture")
    {
        try
        {
            $payment = $order->getPayment();
            $payment->setTransactionId($transactionId);
            $payment->setParentTransactionId(null);
            $transaction = $payment->addTransaction($transactionType, null, true);
            return  $transaction;
        }
        catch (Exception $e)
        {
            $this->logError($e->getMessage(), $e->getTraceAsString());
        }
    }

    public function getOrderTransactions($order)
    {
        $transactions = $this->transactionSearchResultFactory->create()->addOrderIdFilter($order->getId());
        return $transactions->getItems();
    }

    // $orderItemQtys = [$orderItem->getId() => int $qty, ...]
    public function invoiceOrderItems($order, $orderItemQtys, $save = true)
    {
        if (empty($orderItemQtys))
            return null;

        $invoice = $this->invoiceService->prepareInvoice($order, $orderItemQtys);
        $invoice->register();
        $order->setIsInProcess(true);

        if ($save)
        {
            $dbTransaction = $this->transactionFactory->create();
            $dbTransaction->addObject($invoice)->addObject($order)->save();
        }

        return $invoice;
    }

    public function getQuoteFromOrder($order)
    {
        if (!$order->getQuoteId())
            $this->dieWithError("The order has no associated quote ID.");

        return $this->loadQuoteById($order->getQuoteId());
    }

    public function getAmountCaptured($order, $refundSessionId)
    {
        $paymentIntents = $this->getOrderPaymentIntents($order, $refundSessionId);

        $amount = 0;

        foreach ($paymentIntents as $pi)
        {
            foreach ($pi->charges->data as $charge)
            {
                $amount += $charge->amount_captured;
            }
        }

        return $amount;
    }

    public function getAmountAuthorized($order, $refundSessionId)
    {
        $paymentIntents = $this->getOrderPaymentIntents($order, $refundSessionId);

        $amount = 0;

        foreach ($paymentIntents as $pi)
        {
            foreach ($pi->charges->data as $charge)
            {
                if (!$charge->captured && !$charge->refunded)
                    $amount += $charge->amount;
            }
        }

        return $amount;
    }

    public function getAmountRefunded($order, $refundSessionId)
    {
        $paymentIntents = $this->getOrderPaymentIntents($order, $refundSessionId);

        $amount = 0;

        foreach ($paymentIntents as $pi)
        {
            foreach ($pi->charges->data as $charge)
            {
                $amount += $charge->amount_refunded;
            }
        }

        return $amount;
    }

    // If this is called multiple times and would like to invalidate the cache, pass a different $refundSessionId
    public function getOrderPaymentIntents($order, $refundSessionId)
    {
        $cacheKey = $order->getIncrementId() . "_" . $refundSessionId;

        if (!empty($this->orderPaymentIntents[$cacheKey]))
            return $this->orderPaymentIntents[$cacheKey];
        else
            $this->orderPaymentIntents[$cacheKey] = [];

        $paymentIntentIds = [];
        $transactions = $this->getOrderTransactions($order);
        foreach ($transactions as $transaction)
        {
            $id = $this->cleanToken($transaction->getTxnId());
            if ($id)
                $paymentIntentIds[$id] = $id;
        }

        $lastTransId = $this->cleanToken($order->getPayment()->getLastTransId());
        if ($lastTransId)
            $paymentIntentIds[$id] = $id;

        foreach ($paymentIntentIds as $id)
        {
            $pi = \StripeIntegration\Payments\Model\Config::$stripeClient->paymentIntents->retrieve($id, []);
            $this->orderPaymentIntents[$cacheKey][$id] = $pi;
        }

        return $this->orderPaymentIntents[$cacheKey];
    }

    public function setTotalPaid(&$order, $amount, $currency)
    {
        $currency = strtolower($currency);

        if ($currency == strtolower($order->getBaseCurrencyCode()))
        {
            $order->setBaseTotalPaid($amount);
            $rate = $order->getBaseToOrderRate();
            if (empty($rate))
                $rate = 1;
            $order->setTotalPaid($amount * $rate);
        }
        else if ($currency == strtolower($order->getOrderCurrencyCode()))
        {
            $order->setTotalPaid($amount);

            // We should not try to set the base total paid because it may result in a tax rounding error
            // It is best to leave Magento manage the base amount
            /*
            $baseTransactionsTotal = $this->convertOrderAmountToBaseAmount($amount, $currency, $order);
            $order->setBaseTotalPaid($baseTransactionsTotal);
            */
        }
        else
            throw new \Exception("Currency code $currency was not used to place order #" . $order->getIncrementId());

        $order->save();
        $this->orderRepository->save($order);
    }

    public function isPendingCheckoutOrder($order)
    {
        $method = $order->getPayment()->getMethod();
        if (!$this->isStripeCheckoutMethod($method))
            return false;

        if ($order->getState() != "new")
            return false;

        if ($order->getPayment()->getLastTransId())
            return false;

        return true;
    }

    public function clearCache()
    {
        $this->products = [];
        $this->orders = [];
        unset($this->_hasSubscriptions);
        $this->orderPaymentIntents = [];
        return $this;
    }

    public function saveOrder($order)
    {
        $this->orderRepository->save($order);
    }

    public function saveInvoice($invoice)
    {
        $this->invoiceRepository->save($invoice);
    }
}
