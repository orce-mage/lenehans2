<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Quote;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GetQuoteInfo extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;
    protected $quoteIdMaskFactory;
    protected $_formKeyValidator;

    public function __construct(
        Session $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax() && $this->_formKeyValidator->validate($this->getRequest())) {
            $quoteId = $this->checkoutSession->getQuoteId();
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
            $id = $quoteIdMask->getMaskedId();
            $result = $this->resultFactory->create("json");
            return $result->setData([
                'quote_id' => $id
            ]);
        }
        return $this->_redirect("");
    }
}
