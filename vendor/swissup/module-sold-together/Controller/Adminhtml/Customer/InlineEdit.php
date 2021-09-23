<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

use Swissup\SoldTogether\Api\Data\CustomerInterface;
use Swissup\SoldTogether\Api\CustomerRepositoryInterface;

class InlineEdit extends \Magento\Backend\App\Action
{
    protected $_dataProcessor;
    protected $_customerRepository;
    protected $_jsonResult;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PostCustomerProcessor $dataProcessor,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResult
    ) {
        parent::__construct($context);
        $this->_dataProcessor = $dataProcessor;
        $this->_customerRepository = $customerRepository;
        $this->_jsonResult = $jsonResult;
    }

    public function execute()
    {
        $errors = false;
        $messages = [];
        $result = $this->_jsonResult->create();

        $customerItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($customerItems))) {
            return $result->setData([
                'messages' => [__('Please correct the customer data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($customerItems) as $customerId) {
            $customer = $this->_customerRepository->getById($customerId);
            try {
                $customerData = $this->filterCustomerPost($customerItems[$customerId]);
                $this->validatePost($customerData, $customer, $errors, $messages);
                $extendedCustomerData = $customer->getData();
                $this->setSoldtogetherCustomerData($customer, $extendedCustomerData, $customerData);
                $this->_customerRepository->save($customer);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithCustomerId($customer, $e->getMessage());
                $errors = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithCustomerId($customer, $e->getMessage());
                $errors = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithCustomerId(
                    $customer,
                    __('Something went wrong while saving the customer data.')
                );
                $errors = true;
            }
        }

        return $result->setData([
            'messages' => $messages,
            'error' => $errors
        ]);
    }

    protected function filterCustomerPost($customerPostData = [])
    {
        return $this->_dataProcessor->filter($customerPostData);
    }

    protected function validatePost(array $customerData, \Swissup\SoldTogether\Model\Customer $customer, &$errors, array &$messages)
    {
        if (!($this->_dataProcessor->validate($customerData) && $this->_dataProcessor->validateRequireEntry($customerData))) {
            $errors = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithCustomerId($customer, $error->getText());
            }
        }
    }

    protected function getErrorWithCustomerId(CustomerInterface $customer, $errorText)
    {
        return '[Customer ID: ' . $customer->getId() . '] ' . $errorText;
    }

    public function setSoldtogetherCustomerData(\Swissup\SoldTogether\Model\Customer $customer, array $extendedCustomerData, array $customerData)
    {
        $customerData['is_admin'] = 1;
        $customerData['store_id'] = 0;
        $customer->setData(array_merge($customer->getData(), $extendedCustomerData, $customerData));
        return $this;
    }
}
