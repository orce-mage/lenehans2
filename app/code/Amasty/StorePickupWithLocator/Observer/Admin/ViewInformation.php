<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Observer\Admin;

use Amasty\StorePickupWithLocator\Block\Adminhtml\Sales\Order\DateTime;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ViewInformation for show Date/Time in admin, 'core_layout_render_element' event
 */
class ViewInformation implements ObserverInterface
{
    /**
     * Block Name For Displayed In Order Informations
     */
    const BLOCK_NAME = 'amasty_storepickup_datetime';

    /**
     * Name Method For Render Additional Block
     */
    const SHIPPING_NAME = 'amstorepickup_amstorepickup';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        ConfigProvider $configProvider,
        RequestInterface $request
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->configProvider->isStorePickupEnabled()) {
            $elementName = $observer->getElementName();
            $transport = $observer->getTransport();
            $block = $observer->getLayout()->getBlock($elementName);
            if ($elementName === 'order_shipping_view' && $this->configProvider->isPickupDateEnabled()) {
                if ($block->hasData(self::BLOCK_NAME)) {
                    return;
                }
                $html = $transport->getOutput();

                $deliveryBlock = $observer->getLayout()
                    ->createBlock(DateTime::class);

                $html .= $deliveryBlock->toHtml();

                $block->setData(self::BLOCK_NAME, true);

                $transport->setOutput($html);
            }

            if ($elementName === 'shipping_method') {
                $orderCreateRequest = $this->request->getParam('order');
                $quote = $observer->getLayout()->getBlock('shipping_method')->getQuote();
                if ($quote !== null) {
                    $selectedShipping = $quote->getShippingAddress()->getShippingMethod();
                    if (($block->getParentBlock() instanceof \Magento\Sales\Block\Adminhtml\Order\Create\Data
                            || $block->getParentBlock() instanceof \Magento\Sales\Block\Adminhtml\Order\Create\Load)
                        && ((isset($orderCreateRequest['shipping_method'])
                                && $orderCreateRequest['shipping_method'] === self::SHIPPING_NAME)
                            || (!empty($selectedShipping) && $selectedShipping === self::SHIPPING_NAME))
                    ) {
                        $html = $transport->getOutput();
                        $insert = $observer->getLayout()
                            ->createBlock(
                                \Amasty\StorePickupWithLocator\Block\Adminhtml\Sales\Order\Create\Shipping\Form::class
                            );

                        $html = $html . $insert->toHtml();
                        $transport->setOutput($html);
                    }
                }
            }
        }
    }
}
