<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Customer;

use Magenest\StripePayment\Controller\Customer\Card as Card;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Del extends Card
{
    public function execute()
    {
        /**
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
         */
        try {
            $customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            if ($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getCustomerId();
                $id = $this->getRequest()->getParam('id');
                $cardModel = $this->_cardFactory->create();
                $data = $cardModel->load($id);

                $cardId = $data->getData('card_id');
                $tableMagentoCustomerId = $data->getData('magento_customer_id');
                if ($tableMagentoCustomerId == $customerId) {
                    $customer = $this->_customerFactory->create()->load($tableMagentoCustomerId, 'magento_customer_id');
                    $stripeCustomerId = $customer->getData('stripe_customer_id');
                    $response = $this->_helper->deleteCard($stripeCustomerId, $cardId);
                    if (isset($response['id'])) {
                        //delete card success -> delete from db
                        $cardModel->delete();
                        $this->messageManager->addSuccessMessage(__("Delete card success"));
                    }
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Error"));
        } finally {
            return $resultRedirect->setPath('stripe/customer/card');
        }
    }
}
