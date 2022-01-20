<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class AddStripeButton implements ObserverInterface
{
    protected $stripeConfig;
    protected $shortcutFactory;

    public function __construct(
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magenest\StripePayment\Helper\Shortcut\Factory $shortcutFactory
    ) {
        $this->stripeConfig = $stripeConfig;
        $this->shortcutFactory = $shortcutFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($this->stripeConfig->isApplePayActive()) {
            $params = [
                'shortcutValidator' => $this->shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
            ];
            $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();

            /** @var \Magento\Framework\View\Element\Template $shortcut */
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                \Magenest\StripePayment\Block\Payment\Button::class,
                '',
                $params
            );

            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );

            $shortcut->setIsCart(get_class($shortcutButtons) == \Magento\Checkout\Block\QuoteShortcutButtons::class);

            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
