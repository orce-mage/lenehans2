<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice;

class InvoiceExtensionRegistry
{
    /**
     * @var Invoice|null
     */
    protected $gCardInvoice = null;

    /**
     * @param Invoice $gCardInvoice
     */
    public function setCurrentGiftCardInvoice(Invoice $gCardInvoice)
    {
        $this->gCardInvoice = $gCardInvoice;
    }

    /**
     * @return Invoice|null
     */
    public function getCurrentGiftCardInvoice()
    {
        return $this->gCardInvoice;
    }
}
