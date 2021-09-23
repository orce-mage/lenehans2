<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Repository;
use Magento\Quote\Api\Data\CartInterface;

class SaveHandler
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @param CartInterface $quote
     *
     * @return CartInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function saveAttributes(CartInterface $quote): CartInterface
    {
        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return $quote;
        }
        $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();

        if ($gCardQuote->getGiftCards()) {
            $this->repository->save($gCardQuote);
        } elseif ($gCardQuote->getEntityId()) {
            $this->repository->delete($gCardQuote);
        }

        return $quote;
    }
}
