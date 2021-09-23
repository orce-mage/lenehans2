<?php

namespace Magetrend\Email\Block\Email\Block\Sales;

use Magento\Framework\View\Element\Template;

class GiftMessage extends \Magetrend\Email\Block\Email\Block\Template
{
    public $orderRepository;

    public $messageFactory;

    public $registry;

    public function __construct(
        Template\Context $context,
        \Magento\GiftMessage\Model\OrderRepository $orderRepository,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->messageFactory = $messageFactory;
        parent::__construct($context, $data);
    }

    public function getMessage()
    {
        $order = $this->getOrder();
        $message = false;
        if ($order->getGiftMessageId()) {
            $message = $this->orderRepository->get($order->getId());
        } elseif ($this->registry->registry('mt_editor_edit_mode') == 1) {
            $message = $this->getDemoMessage();
        }

        return $message;
    }

    public function getDemoMessage()
    {
        $message = $this->messageFactory->create();
        $message->setData([
            'customer_id' => '0',
            'sender' => 'John Doe',
            'recipient' => 'Veronica Costelo',
            'message' => 'Lorem Ipsum is simply dummy text',
        ]);

        return $message;
    }
}