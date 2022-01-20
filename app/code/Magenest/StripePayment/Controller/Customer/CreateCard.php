<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Customer;

use Magento\Framework\App\Action\Context;

class CreateCard extends \Magento\Framework\App\Action\Action
{
    protected $_formKeyValidator;
    protected $stripeHelper;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {

        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->stripeHelper = $stripeHelper;
        $this->jsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'error' => true,
                'message' => __('Invalid Form Key')
            ]);
        }
        if ($this->getRequest()->isAjax()) {
            $requestResult = $this->getRequest()->getParam('result');
            if (isset($requestResult['source'])) {
                $customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
                $customerId = $customerSession->getCustomerId();
                $stripeResponse = $requestResult['source'];
                if ($this->stripeHelper->saveCard($customerId, $stripeResponse)) {
                    return $result->setData([
                        'error' => false,
                        'success' => true
                    ]);
                } else {
                    return $result->setData([
                        'error' => true,
                        'message' => __('Error')
                    ]);
                }
            }
        }
        return $result->setData([
            'error' => true,
            'message' => __('Invalid request')
        ]);
    }
}
