<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magenest\StripePayment\Helper\Data as DataHelper;
use Magenest\StripePayment\Model\CustomerFactory;

class Card extends Action
{
    protected $_customerSession;

    protected $_cardFactory;

    protected $_resultJsonFactory;

    protected $_config;

    protected $_jsonFactory;

    protected $_helper;

    protected $_customerFactory;

    protected $_stripeModel;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Magenest\StripePayment\Model\CardFactory $cardFactory,
        \Magenest\StripePayment\Helper\Config $config,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        DataHelper $dataHelper,
        \Magenest\StripePayment\Model\CustomerFactory $customerFactory,
        \Magenest\StripePayment\Model\StripePaymentMethod $stripePaymentMethod
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_cardFactory = $cardFactory;
        $this->_config = $config;
        $this->_jsonFactory = $resultJsonFactory;
        $this->_stripeModel = $stripePaymentMethod;
        parent::__construct($context);
        $this->_helper = $dataHelper;
    }

    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Stripe Stored Cards'));
        $this->_view->renderLayout();
    }
}
