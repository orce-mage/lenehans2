<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Api\LocationPickupValuesInterface;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\Quote\CurbsideValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ShippingAddressManagementInterface;

class LocationPickupValues implements LocationPickupValuesInterface
{
    /**
     * @var QuoteRepositoryInterface
     */
    private $amQuoteRepository;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var DateTimeValidator
     */
    private $validator;

    /**
     * @var CurbsideValidator
     */
    private $curbsideValidator;

    public function __construct(
        QuoteRepositoryInterface $amQuoteRepository,
        ShippingAddressManagementInterface $shippingAddressManagement,
        DateTimeValidator $validator,
        CurbsideValidator $curbsideValidator
    ) {
        $this->amQuoteRepository = $amQuoteRepository;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->validator = $validator;
        $this->curbsideValidator = $curbsideValidator;
    }

    /**
     * @deprecated
     * @param int $cartId
     * @param int $locationId
     * @param string|null $date
     * @param string|null $timePeriod
     *
     * @return bool|mixed
     * @throws InputException
     */
    public function saveSelectedPickupValues(
        $cartId,
        $locationId,
        $date = null,
        $timePeriod = null
    ) {
        $timeFrom = $timeTo = null;

        if ($date) {
            if ($timePeriod) {
                list($timeFrom, $timeTo) = explode('|', $timePeriod);
            }

            if (!$this->validator->isValidDate($cartId, $locationId, $date, $timeFrom, $timeTo)) {
                throw new InputException(__('Store Pickup Date/Time is not valid.'));
            }
        }

        $this->processSavePickupData((int)$cartId, (int)$locationId, $date, (int)$timeFrom, (int)$timeTo);

        return true;
    }

    /**
     * @param int $cartId
     * @param QuoteInterface $quotePickupData
     * @return bool
     */
    public function saveSelectedPickupData(
        int $cartId,
        QuoteInterface $quotePickupData
    ): bool {
        $date = $quotePickupData->getDate();
        $timeFrom = (int)$quotePickupData->getTimeFrom();
        $timeTo = (int)$quotePickupData->getTimeTo();
        $locationId = (int)$quotePickupData->getStoreId();

        if ($date) {
            if (!$this->validator->isValidDate($cartId, $locationId, $date, $timeFrom, $timeTo)) {
                throw new InputException(__('Store Pickup Date/Time is not valid.'));
            }
        }
        $this->curbsideValidator->validateComment($quotePickupData);

        $isCurbside = $this->curbsideValidator->shouldSaveCurbsideValue($quotePickupData)
            ? (bool)$quotePickupData->getIsCurbsidePickup()
            : false;
        $comment = $this->curbsideValidator->shouldSaveComment($quotePickupData)
            ? $quotePickupData->getCurbsidePickupComment()
            : '';

        $this->processSavePickupData(
            $cartId,
            $locationId,
            $date,
            $timeFrom,
            $timeTo,
            $isCurbside,
            $comment
        );

        return true;
    }

    /**
     * @param int $cartId
     * @param int|null $locationId
     * @param string|null $date
     * @param int|null $timeFrom
     * @param int|null $timeTo
     * @param bool $isCurbside
     * @param string $curbsideComment
     * @return void
     * @throws LocalizedException
     */
    private function processSavePickupData(
        int $cartId,
        ?int $locationId,
        ?string $date,
        ?int $timeFrom,
        ?int $timeTo,
        bool $isCurbside = false,
        string $curbsideComment = ''
    ): void {
        $addressEntity = $this->shippingAddressManagement->get($cartId);
        $addressId = $addressEntity->getId();
        $quoteEntity = $this->amQuoteRepository->getByAddressId($addressId);

        $quoteEntity->setAddressId($addressId)
            ->setStoreId($locationId)
            ->setQuoteId($cartId)
            ->setDate($date)
            ->setTimeFrom($timeFrom)
            ->setTimeTo($timeTo)
            ->setIsCurbsidePickup($isCurbside)
            ->setCurbsidePickupComment($curbsideComment);

        $this->amQuoteRepository->save($quoteEntity);
    }
}
