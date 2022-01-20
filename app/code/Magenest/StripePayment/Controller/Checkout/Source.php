<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magento\Framework\Exception\LocalizedException;
use Stripe;
use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\QuoteValidator;

abstract class Source extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $stripeConfig;
    protected $storeManagerInterface;
    protected $stripeLogger;
    protected $_formKeyValidator;
    protected $stripeHelper;
    protected $customerSession;
    protected $sourceFactory;
    protected $quoteValidator;
    protected $customerManagement;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory,
        CustomerManagement $customerManagement,
        QuoteValidator $quoteValidator
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->stripeConfig = $stripeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->stripeLogger = $stripeLogger;
        $this->_formKeyValidator = $formKeyValidator;
        $this->stripeHelper = $stripeHelper;
        $this->customerSession = $customerSession;
        $this->sourceFactory = $sourceFactory;
        $this->quoteValidator = $quoteValidator;
        $this->customerManagement = $customerManagement;
    }

    public function execute()
    {
        $this->_debug("Creating source");
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            if (!class_exists(Stripe\Stripe::class)) {
                throw new StripePaymentException(
                    __("Stripe PHP library was not installed")
                );
            }
            $this->stripeHelper->initStripeApi();
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new StripePaymentException(
                    __("Invalid form key")
                );
            }
            $quote = $this->_checkoutSession->getQuote();
            $this->quoteValidator->validateBeforeSubmit($quote);
            if (!$quote->getCustomerIsGuest()) {
                if ($quote->getCustomerId()) {
                    if (method_exists($this->customerManagement, 'validateAddresses')) {
                        $this->customerManagement->validateAddresses($quote);
                    }
                }
            }
            $request = $this->getPostRequest($quote);
            $source = Stripe\Source::create($request);
            $this->_debug($source->getLastResponse()->json);
            if ($this->getSourceType() == 'wechat') {
                $redirectUrl = $source->wechat->qr_code_url;
            } else {
                $redirectUrl = $source->redirect->url;
            }
            $sourceId = $source->id;
            $payment = $quote->getPayment();
            $sourceModel = $this->sourceFactory->create();
            $sourceModel->setData("source_id", $sourceId);
            $sourceModel->setData("method", $payment->getMethod());
            $sourceModel->setData("quote_id", $quote->getEntityId());
            $sourceModel->isObjectNew(true);
            $sourceModel->save();
            $payment->setAdditionalInformation("stripe_uid", uniqid());
            $quote->setIsActive(false)->save();

            $data = [
                'success' => true,
                'error' => false,
                'redirect_url' => $redirectUrl
            ];
            $result->setData($data);
        } catch (Stripe\Exception\ApiErrorException $e) {
            $result->setData([
                'error' => true,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Magenest\StripePayment\Exception\StripePaymentException $e) {
            $result->setData([
                'error' => true,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $this->stripeHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        } catch (LocalizedException $e) {
            $this->stripeHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->stripeHelper->debugException($e);
            $result->setData([
                'error' => true,
                'success' => false,
                'message' => __("Cannot process payment")
            ]);
        } finally {
            return $result;
        }
    }

    /**
     * @var \Magento\Quote\Model\Quote $quote
     * @return array
     */
    protected function getPostRequest($quote)
    {
        $request = $this->stripeHelper->getPaymentSource($quote, $this->getSourceType());
        $request = array_merge(
            $request,
            [
                "redirect" => [
                    "return_url" => $this->getReturnUrl()
                ],
            ]
        );
        $request = array_merge($request, $this->getCustomRequest());
        $this->_debug($request);
        return $request;
    }

    abstract protected function getReturnUrl();
    abstract protected function getSourceType();

    /**
     * @return array
     */
    protected function getCustomRequest()
    {
        return [];
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}
