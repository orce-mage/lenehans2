<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

use Swissup\SoldTogether\Api\Data\OrderInterface;
use Swissup\SoldTogether\Api\OrderRepositoryInterface;

class InlineEdit extends \Magento\Backend\App\Action
{
    protected $_dataProcessor;
    protected $_orderRepository;
    protected $_jsonResult;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PostOrderProcessor $dataProcessor,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResult
    ) {
        parent::__construct($context);
        $this->_dataProcessor = $dataProcessor;
        $this->_orderRepository = $orderRepository;
        $this->_jsonResult = $jsonResult;
    }

    public function execute()
    {
        $errors = false;
        $messages = [];
        $result = $this->_jsonResult->create();

        $orderItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($orderItems))) {
            return $result->setData([
                'messages' => [__('Please correct the order data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($orderItems) as $orderId) {
            $order = $this->_orderRepository->getById($orderId);
            try {
                $orderData = $this->filterOrderPost($orderItems[$orderId]);
                $this->validatePost($orderData, $order, $errors, $messages);
                $extendedOrderData = $order->getData();
                $this->setSoldtogetherOrderData($order, $extendedOrderData, $orderData);
                $this->_orderRepository->save($order);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithOrderId($order, $e->getMessage());
                $errors = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithOrderId($order, $e->getMessage());
                $errors = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithOrderId(
                    $order,
                    __('Something went wrong while saving the order data.')
                );
                $errors = true;
            }
        }

        return $result->setData([
            'messages' => $messages,
            'error' => $errors
        ]);
    }

    protected function filterOrderPost($orderPostData = [])
    {
        return $this->_dataProcessor->filter($orderPostData);
    }

    protected function validatePost(array $orderData, \Swissup\SoldTogether\Model\Order $order, &$errors, array &$messages)
    {
        if (!($this->_dataProcessor->validate($orderData) && $this->_dataProcessor->validateRequireEntry($orderData))) {
            $errors = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithOrderId($order, $error->getText());
            }
        }
    }

    protected function getErrorWithOrderId(OrderInterface $order, $errorText)
    {
        return '[Order ID: ' . $order->getId() . '] ' . $errorText;
    }

    public function setSoldtogetherOrderData(\Swissup\SoldTogether\Model\Order $order, array $extendedOrderData, array $orderData)
    {
        $orderData['is_admin'] = 1;
        $orderData['store_id'] = 0;
        $order->setData(array_merge($order->getData(), $extendedOrderData, $orderData));
        return $this;
    }
}
