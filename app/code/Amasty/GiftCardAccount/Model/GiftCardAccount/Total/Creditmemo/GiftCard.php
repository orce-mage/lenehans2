<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Creditmemo;

use Amasty\GiftCardAccount\Model\ConfigProvider;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler as CreditmemoReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;
use Magento\Framework\App\RequestInterface;

class GiftCard extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var OrderReadHandler
     */
    private $orderReadHandler;

    /**
     * @var CreditmemoReadHandler
     */
    private $creditMemoReadHandler;

    public function __construct(
        OrderReadHandler $orderReadHandler,
        CreditmemoReadHandler $creditMemoReadHandler,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderReadHandler = $orderReadHandler;
        $this->creditMemoReadHandler = $creditMemoReadHandler;
    }

    /**
     * Collect gift card account totals for credit memo
     *
     * @inheritDoc
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $this->creditMemoReadHandler->loadAttributes($creditmemo);
        $gCardMemo = $creditmemo->getExtensionAttributes()->getAmGiftcardCreditmemo();

        $order = $creditmemo->getOrder();
        $this->orderReadHandler->loadAttributes($order);
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        if ($gCardOrder->getBaseGiftAmount() && $gCardOrder->getBaseInvoiceGiftAmount() != 0) {
            $gcaLeft = $gCardOrder->getBaseInvoiceGiftAmount() - $gCardOrder->getBaseRefundGiftAmount();
            $creditmemoBaseAmount = $creditmemo->getBaseGrandTotal();

            if ($gcaLeft >= $creditmemoBaseAmount) {
                $baseUsed = $creditmemoBaseAmount;
                $used = $creditmemo->getGrandTotal();

                $creditmemo->setBaseGrandTotal(0);
                $creditmemo->setGrandTotal(0);

                $creditmemo->setAllowZeroGrandTotal(true);
            } else {
                $baseUsed = $gCardOrder->getBaseInvoiceGiftAmount() - $gCardOrder->getBaseRefundGiftAmount();
                $used = $gCardOrder->getInvoiceGiftAmount() - $gCardOrder->getRefundGiftAmount();

                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseUsed);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $used);
            }
            $gCardMemo->setBaseGiftAmount($baseUsed);
            $gCardMemo->setGiftAmount($used);

            $gCardOrder->setBaseRefundGiftAmount((float)($gCardOrder->getBaseRefundGiftAmount() + $baseUsed));
            $gCardOrder->setRefundGiftAmount((float)($gCardOrder->getRefundGiftAmount() + $used));
        }

        return $this;
    }
}
