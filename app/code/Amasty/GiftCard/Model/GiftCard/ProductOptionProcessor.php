<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var GiftCardOptionInterfaceFactory
     */
    private $giftCardOptionFactory;

    public function __construct(
        DataObjectFactory $objectFactory,
        DataObjectHelper $dataObjectHelper,
        GiftCardOptionInterfaceFactory $giftCardOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->giftCardOptionFactory = $giftCardOptionFactory;
    }

    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getAmGiftcardOptions()
        ) {
            $data = $productOption->getExtensionAttributes()
                ->getAmGiftcardOptions()
                ->getData();

            if ($data) {
                $request->addData($data);
            }
        }

        return $request;
    }

    public function convertToProductOption(DataObject $request)
    {
        $requestData = $request->getData();

        if ($requestData) {
            /** @var GiftCardOptionInterface $productOption */
            $productOption = $this->giftCardOptionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $productOption,
                $requestData,
                GiftCardOptionInterface::class
            );

            return ['am_giftcard_options' => $productOption];
        }

        return [];
    }
}
