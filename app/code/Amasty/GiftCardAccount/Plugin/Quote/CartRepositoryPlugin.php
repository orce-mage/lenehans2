<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Quote;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\SaveHandler;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class CartRepositoryPlugin
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    public function __construct(
        ReadHandler $readHandler,
        SaveHandler $saveHandler
    ) {
        $this->readHandler = $readHandler;
        $this->saveHandler = $saveHandler;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     *
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        $this->readHandler->loadAttributes($quote);

        return $quote;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param SearchResultsInterface $searchResult
     *
     * @return SearchResultsInterface
     */
    public function afterGetList(
        CartRepositoryInterface $subject,
        SearchResultsInterface $searchResult
    ): SearchResultsInterface {
        $quotes = [];

        foreach ($searchResult->getItems() as $quote) {
            $this->readHandler->loadAttributes($quote);
            $quotes[] = $quote;
        }
        $searchResult->setItems($quotes);

        return $searchResult;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        $this->saveHandler->saveAttributes($quote);
    }
}
