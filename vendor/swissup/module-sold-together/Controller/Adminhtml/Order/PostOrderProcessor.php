<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

class PostOrderProcessor extends \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor
{
    public function filter($data)
    {
        $inputFilter = new \Zend_Filter_Input([], [], $data);
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    public function validateRequireEntry(array $data)
    {
        $requiredOrderFields = ['weight' => __('Order Weight')];
        $errors = true;
        foreach ($data as $orderField => $item) {
            if (in_array($orderField, array_keys($requiredOrderFields)) && $item == '') {
                $errors = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredOrderFields[$orderField])
                );
            }
        }
        return $errors;
    }
}
