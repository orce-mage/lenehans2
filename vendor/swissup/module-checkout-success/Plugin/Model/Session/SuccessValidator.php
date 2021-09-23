<?php

namespace Swissup\CheckoutSuccess\Plugin\Model\Session;

use Magento\Framework\App\Config\ScopeConfigInterface;

class SuccessValidator
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    private $configModel;

    /**
     * Plugin constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ValueInterface $configModel
    ) {
        $this->request = $request;
        $this->session = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->configModel = $configModel;
    }

    /**
     * After isValid
     * @param  \Magento\Checkout\Model\Session\SuccessValidator $subject
     * @param  bool $result
     * @return bool
     */
    public function afterIsValid(
        \Magento\Checkout\Model\Session\SuccessValidator $subject,
        $result
    ) {
        if (!$result) {
            // $result is FALSE - Magento will redirect to cart
            $id = $this->request->getParam('previewObjectId');
            if (!empty($id)) {
                $order = $this->getOrderToPreview($id);
                if ($order->getId()) {
                    $result = true;
                    $this->session->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastOrderStatus($order->getStatus());
                }
            }
        }

        if (!$this->session->getLastOrderId()) {
            $result = false;
        }

        return $result;
    }

    /**
     * Get order by its external ID
     *
     * @param  srting $incrementId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderToPreview($incrementId)
    {
        $order = $this->orderFactory->create();
        if ($this->canLoadOrder($this->request->getParam('hash'))) {
            $order->loadByIncrementId($incrementId);
        }

        return $order;
    }

    /**
     * Check hash and time if success preview is allowed
     * @param  string $hash
     * @return boolean
     */
    protected function canLoadOrder($hash)
    {
        // read config values directly from table to ignore cache (just in case)
        $config = $this->configModel->getCollection()->addFieldToFilter(
            'path',
            ['in' => [
                    'success_page/layout/preview_hash',
                    'success_page/layout/preview_expires'
                ]
            ]
        );
        // find row with hash
        foreach ($config->getData() as $row) {
            if ($row['value'] == $hash) {
                $previewHash = $row;
                break;
            }
        }
        if (isset($previewHash)) {
            // find related row with expire time
            foreach ($config->getData() as $row) {
                if ($row['path'] == 'success_page/layout/preview_expires'
                    && $row['scope'] == $previewHash['scope']
                    && $row['scope_id'] == $previewHash['scope_id']
                ) {
                    $previewExpires = $row;
                    $currentTime = time();
                    break;
                }
            }
        }

        return isset($previewExpires)
            ? ($previewExpires['value'] >= $currentTime)
            : false;
    }
}
