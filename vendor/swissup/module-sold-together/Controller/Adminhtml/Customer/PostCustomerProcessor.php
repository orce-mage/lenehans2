<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

class PostCustomerProcessor extends \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor
{
    public function filter($data)
    {
        $inputFilter = new \Zend_Filter_Input([], [], $data);
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    public function validateRequireEntry(array $data)
    {
        $requiredCustomerFields = ['weight' => __('Customer Weight')];
        $errors = true;
        foreach ($data as $customerField => $item) {
            if (in_array($customerField, array_keys($requiredCustomerFields)) && $item == '') {
                $errors = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredCustomerFields[$customerField])
                );
            }
        }
        return $errors;
    }
}
